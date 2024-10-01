<?xml version="1.0" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../banshee/main.xslt" />
<xsl:import href="../banshee/splitform.xslt" />

<!--
//
//  Layout templates
//
//-->
<xsl:template name="splitform_header">
<h1>Splitform library demo</h1>
</xsl:template>

<xsl:template name="splitform_footer">
<div>Progress: form <xsl:value-of select="current + 1" /> of <xsl:value-of select="current/@max + 1" /></div>
</xsl:template>

<!--
//
//  Form one template
//
//-->
<xsl:template match="splitform/one">
<label for="name">Name:</label>
<input type="text" id="name" name="name" value="{name}" class="form-control" />
<label for="number">Number:</label>
<input type="text" id="number" name="number" value="{number}" class="form-control" />
</xsl:template>

<!--
//
//  Form two template
//
//-->
<xsl:template match="splitform/two">
<label for="title">Title:</label>
<input type="text" name="title" value="{title}" class="form-control" />
<label for="content">Content:</label>
<textarea id="content" name="content" class="form-control"><xsl:value-of select="content" /></textarea>
</xsl:template>

<!--
//
//  Form three template
//
//-->
<xsl:template match="splitform/three">
<label for="remark">Remark:</label>
<input type="text" id="remark" name="remark" value="{remark}" class="form-control" />
</xsl:template>

<!--
//
//  Process template
//
//-->
<xsl:template match="submit">
<xsl:call-template name="splitform_header" />
<xsl:call-template name="progressbar" />
<p>Your information has been processed.</p>

<h3>Values</h3>
<table class="table table-striped table-condensed">
<thead>
<tr><th>Key</th><th>Value</th></tr>
</thead>
<tbody>
<xsl:for-each select="value">
<tr><td><xsl:value-of select="@key" />:</td><td><xsl:value-of select="." /></td></tr>
</xsl:for-each>
</tbody>
</table>

<div class="btn-group">
<a href="/demos" class="btn btn-default">Continue</a>
</div>
</xsl:template>

</xsl:stylesheet>
