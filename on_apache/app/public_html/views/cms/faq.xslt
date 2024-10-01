<?xml version="1.0" ?>
<!--
//
//  Copyright (c) by Hugo Leisink <hugo@leisink.net>
//  This file is part of the Banshee PHP framework
//  https://gitlab.com/hsleisink/banshee/
//
//  Licensed under The MIT License
//
//-->
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../banshee/main.xslt" />

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<xsl:for-each select="sections/section">
	<xsl:variable name="section_id" select="@id" />
	<h2><xsl:value-of select="." /></h2>
	<table class="table table-striped table-hover table-condensed">
	<thead>
	<tr><th>Question</th></tr>
	</thead>
	<tbody>
	<xsl:for-each select="../../faqs/faq[section_id=$section_id]">
		<tr onClick="javascript:document.location='/{/output/page}/{@id}'"><td><xsl:value-of select="question" /></td></tr>
	</xsl:for-each>
	</tbody>
	</table>
</xsl:for-each>

<div class="btn-group">
<a href="/{/output/page}/new" class="btn btn-default">New FAQ</a>
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
<xsl:if test="faq/@id">
<input type="hidden" name="id" value="{faq/@id}" />
</xsl:if>
<label for="question">Question:</label>
<input type="text" id="question" name="question" value="{faq/question}" class="form-control" />
<label for="editor">Answer:</label>
<textarea id="editor" name="answer" class="form-control"><xsl:value-of select="faq/answer" /></textarea>

<label for="section">Section:</label>
<xsl:if test="count(sections/section)>0">
<span>new <input type="radio" id="select_new" name="select" value="new" onChange="javascript:set_section_type()">
<xsl:if test="faq/select='new'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
</input></span>
<span>existing <input type="radio" id="select_existing" name="select" value="existing" onClick="javascript:set_section_type()">
<xsl:if test="faq/select='existing'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
</input></span>

<xsl:if test="sections/section">
<div><select id="input_existing" name="section_id" class="form-control">
<xsl:for-each select="sections/section">
<option value="{@id}"><xsl:if test="@id=../../faq/section_id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if><xsl:value-of select="." /></option>
</xsl:for-each>
</select></div>
</xsl:if>
</xsl:if>
<input type="text" id="input_new" name="label" value="{faq/label}" class="form-control" />

<div class="btn-group">
<input type="submit" name="submit_button" value="Save FAQ" class="btn btn-default" />
<a href="/{/output/page}" class="btn btn-default">Back</a>
<xsl:if test="faq/@id">
<input type="submit" name="submit_button" value="Delete FAQ" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
</xsl:if>
</div>
</form>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<img src="/images/icons/faq.png" class="title_icon" />
<h1>F.A.Q. Administration</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
