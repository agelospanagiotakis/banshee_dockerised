<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../banshee/main.xslt" />
<xsl:import href="../banshee/pagination.xslt" />

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<table class="table table-condensed table-striped table-hover">
<thead>
<tr>
<th>Title</th><th>Active</th><th>Answers</th>
</tr>
</thead>
<tbody>
<xsl:for-each select="questionnaires/questionnaire">
<tr class="click">
<xsl:if test="answers='0'">
<xsl:attribute name="onClick">javascript:document.location='/<xsl:value-of select="/output/page" />/<xsl:value-of select="@id" />'</xsl:attribute>
</xsl:if>
<xsl:if test="answers!='0'">
<xsl:attribute name="onClick">javascript:document.location='/<xsl:value-of select="/output/page" />/view/<xsl:value-of select="@id" />'</xsl:attribute>
</xsl:if>
<td><xsl:value-of select="title" /></td>
<td><xsl:value-of select="active" /></td>
<td><xsl:value-of select="answers" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>

<div class="right">
<xsl:apply-templates select="pagination" />
</div>

<div class="btn-group left">
<a href="/{/output/page}/new" class="btn btn-default">New questionnaire</a>
<a href="/cms" class="btn btn-default">Back</a>
</div>
</xsl:template>

<!--
//
//  Edit template
//
//-->
<xsl:template match="edit">
<xsl:call-template name="show_messages" />
<form action="/{/output/page}" method="post">
<input type="hidden" name="answers" value="{questionnaire/answers}" />
<input type="hidden" name="activated" value="{questionnaire/activated}" />
<xsl:if test="questionnaire/@id">
<input type="hidden" name="id" value="{questionnaire/@id}" />
</xsl:if>

<label for="title">Title:</label>
<input type="text" id="title" name="title" value="{questionnaire/title}" class="form-control" />
<label for="intro">Introduction:</label>
<textarea id="intro" name="intro" class="form-control"><xsl:value-of select="questionnaire/intro" /></textarea>
<label for="form">Form:</label>
<textarea id="form" name="form" class="form-control"><xsl:if test="questionnaire/answers>0 or questionnaire/activated='yes'"><xsl:attribute name="readonly">readonly</xsl:attribute></xsl:if><xsl:value-of select="questionnaire/form" /></textarea>
<label for="submit">Submit button label:</label>
<input type="text" id="submit" name="submit" value="{questionnaire/submit}" class="form-control" />
<label for="after">Text after submit:</label>
<textarea id="after" name="after" class="form-control"><xsl:value-of select="questionnaire/after" /></textarea>
<label for="access_code">Access code:</label>
<input type="text" id="access_code" name="access_code" value="{questionnaire/access_code}" class="form-control" />
<label for="active">Questionnaire active:</label>
<input type="checkbox" id="active" name="active"><xsl:if test="questionnaire/active='yes'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input>

<div class="btn-group">
<input type="submit" name="submit_button" value="Save questionnaire" class="btn btn-default" />
<a href="/{/output/page}" class="btn btn-default">Cancel</a>
<xsl:if test="questionnaire/@id">
<xsl:if test="questionnaire/answers!='0'">
<input type="submit" name="submit_button" value="Erase answers" class="btn btn-default" onClick="javascript:return confirm('ERASE: Are you sure?')" />
</xsl:if>
<input type="submit" name="submit_button" value="Delete questionnaire" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
</xsl:if>
</div>
</form>

<div id="help">
<p>The form field must be filled with the format of the questionnaire. It must contain one or more blocks of lines, each separated by an empty line. The format of a block is as follows:</p>
<pre class="code">
&lt;question&gt;
line|text|select|checkbox|radio [required]
[option]
[option]
...
</pre>
<p>The first line holds the question and can contain any text you like.</p>
<p>The second line contains the type of that question, optionally followed by 'required' to make answering this question mandatory. The type can be one of the following options:</p>
<ul>
<li><b>line:</b> A single text line.</li>
<li><b>text:</b> A text block for multiple lines of text.</li>
<li><b>select:</b> A pulldown to select a single option.</li>
<li><b>checkbox:</b> A list of options, each selectable.</li>
<li><b>radio:</b> A list of options, only one selectable. Use 'other' as an option for a free-text line.</li>
</ul>
</div>
</xsl:template>

<!--
//
//  Answers template
//
//-->
<xsl:template match="answers">

<!-- Filter -->
<xsl:if test="filter">
<form action="/{/output/page}/view/{@id}" method="post" class="filter">
<div class="panel panel-default">
<div class="panel-heading" onClick="javascript:$('div.panel-body').toggle()">Filter</div>
<div class="panel-body">
<div class="row">
<xsl:for-each select="filter">
<div class="col-sm-6 question">
<xsl:value-of select="question" />
</div>
<div class="col-sm-6">
<select name="filter[{@name}]" class="form-control" onChange="javascript:submit()">
<option value=""></option>
<xsl:for-each select="option">
<option value="{position()-1}"><xsl:if test="@selected='yes'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if><xsl:value-of select="." /></option>
</xsl:for-each>
</select>
</div>
</xsl:for-each>
</div>
</div>
</div>
<input type="hidden" name="submit_button" value="Filter" />
</form>
</xsl:if>

<!-- Results -->
<p>The result of <xsl:value-of select="@count" /> submits:</p>

<xsl:for-each select="question">
<h2><xsl:value-of select="text" /></h2>
<xsl:choose>
<!-- line and text -->
<xsl:when test="@type='line' or @type='text'">
<button class="btn btn-default btn-xs open" onClick="javascript:$('div#{@name}').css('max-height', 'none'); $(this).remove();">+</button>
<div id="{@name}" class="scroll">
<xsl:for-each select="answer">
<xsl:if test=".!=''">
<div class="well"><xsl:value-of select="." /></div>
</xsl:if>
</xsl:for-each>
</div>
</xsl:when>
<!-- select and checkbox -->
<xsl:when test="@type='select' or @type='checkbox' or @type='radio'">
<table class="table table-striped table-condensed">
<thead>
<tr><th>Option</th><th>Count</th><th>Percentage</th></tr>
</thead>
<tbody>
<xsl:for-each select="option">
<xsl:sort order="descending" data-type="number" select="@count" />
<tr>
<td><xsl:value-of select="." /></td>
<td><xsl:value-of select="@count" /></td>
<td><xsl:value-of select="@perc" />%</td>
</tr>
</xsl:for-each>
</tbody>
<tfoot>
<tr><td>Total:</td><td><xsl:value-of select="sum(option/@count)" /></td><td></td></tr>
</tfoot>
</table>
<!-- radio -->
<xsl:if test="@type='radio'">
<p>Others answers:</p>
<button class="btn btn-default btn-xs open" onClick="javascript:$('div#{@name}').css('max-height', 'none'); $(this).remove();">+</button>
<div id="{@name}" class="scroll">
<xsl:for-each select="other">
<div class="well"><xsl:value-of select="." /></div>
</xsl:for-each>
</div>
</xsl:if>
</xsl:when>
<!-- done -->
</xsl:choose>
</xsl:for-each>

<div class="btn-group">
<a href="/{/output/page}/{@id}" class="btn btn-default">Edit questionnaire</a>
<a href="/{/output/page}" class="btn btn-default">Back</a>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<img src="/images/icons/questionnaire.png" class="title_icon" />
<h1>Questionnaire administration</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="answers" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
