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
<xsl:import href="banshee/main.xslt" />
<xsl:import href="banshee/pagination.xslt" />

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<table class="table table-striped questionnaires">
<thead>
<tr><th>Questionnaire</th></tr>
</thead>
<tbody>
<xsl:for-each select="questionnaire">
<tr onClick="javascript:document.location=$(this).find('a').attr('href')"><td><a href="/{/output/page}/{@id}"><xsl:value-of select="." /></a></td></tr>
</xsl:for-each>
</tbody>
</table>
</xsl:template>

<!--
//
//  Access code template
//
//-->
<xsl:template match="access_code">
<xsl:call-template name="show_messages" />

<form action="{/output/page/@url}" method="post">
<label for="access_code">Access code:</label>
<input type="text" id="access_code" name="access_code" value="{questionnaire/access_code}" class="form-control" />

<div class="btn-group">
<input type="submit" name="submit_button" value="Submit" class="btn btn-default" />
</div>
</form>
</xsl:template>

<!--
//
//  Questionnaire template
//
//-->
<xsl:template match="questionnaire">
<xsl:call-template name="show_messages" />

<xsl:if test="intro"><xsl:value-of disable-output-escaping="yes" select="intro" /></xsl:if>

<form action="{/output/page/@url}" method="post">
<input type="hidden" name="id" value="{@id}" />
<xsl:for-each select="input">
<label for="{@name}"><xsl:value-of select="question" /><xsl:if test="@required='yes'"><span class="required">*</span></xsl:if></label>
<div class="form-group">
<xsl:choose>
	<!-- line -->
	<xsl:when test="@type='line'">
	<input type="text" id="{@name}" name="{@name}" value="{value}" class="form-control" />
	</xsl:when>
	<!-- text -->
	<xsl:when test="@type='text'">
	<textarea id="{@name}" name="{@name}" class="form-control"><xsl:value-of select="value" /></textarea>
	</xsl:when>
	<!-- select -->
	<xsl:when test="@type='select'">
	<select  id="{@name}" name="{@name}" class="form-control">
		<option value=""></option>
		<xsl:for-each select="option">
		<option value="{position()-1}">
			<xsl:if test="(position()-1)=../value">
				<xsl:attribute name="selected">selected</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="." />
		</option>
		</xsl:for-each>
	</select>
	</xsl:when>
	<!-- checkbox -->
	<xsl:when test="@type='checkbox'">
	<xsl:for-each select="option">
	<div>
		<input type="checkbox" name="{../@name}[{position()-1}]" value="on">
			<xsl:if test="@checked='yes'">
				<xsl:attribute name="checked">checked</xsl:attribute>
			</xsl:if>
		</input>
		<xsl:value-of select="." />
	</div>
	</xsl:for-each>
	</xsl:when>
	<!-- radio -->
	<xsl:when test="@type='radio'">
	<xsl:for-each select="option">
	<xsl:if test=".='other'">
	<div>
		<input type="radio" name="{../@name}" value="{position()-1}">
			<xsl:if test="(position()-1)=../value">
				<xsl:attribute name="checked">checked</xsl:attribute>
			</xsl:if>
		</input>Other:
		<input type="text" name="{../@name}_other" value="{@text}" class="form-control" onKeyDown="javascript:$('input[name={../@name}]').prop('checked', true)" />
	</div>
	</xsl:if>
	<xsl:if test=".!='other'">
	<div>
		<input type="radio" name="{../@name}" value="{position()-1}">
			<xsl:if test="(position()-1)=../value">
				<xsl:attribute name="checked">checked</xsl:attribute>
			</xsl:if>
		</input>
		<xsl:value-of select="." />
	</div>
	</xsl:if>
	</xsl:for-each>
	</xsl:when>
	<!-- otherwise -->
	<xsl:otherwise>
	<p>Invalid input type.</p>
	</xsl:otherwise>
</xsl:choose>
</div>
</xsl:for-each>

<xsl:if test="input/@required='yes'">
<p>Questions marked with <span class="required">*</span> are mandatory.</p>
</xsl:if>

<div class="btn-group">
<input type="submit" name="submit_button" value="{submit}" class="btn btn-default" />
</div>
</form>
</xsl:template>

<!--
//
//  After template
//
//-->
<xsl:template match="after">
<xsl:value-of disable-output-escaping="yes" select="." />
</xsl:template>

<!--
//
//  Answers template
//
//-->
<xsl:template match="answers">
<p><xsl:value-of disable-output-escaping="yes" select="after" /></p>

<hr />

<table class="table table-striped answers">
<thead>
<tr><th>Question</th><th>Answer</th></tr>
</thead>
<tbody>
<xsl:for-each select="answer">
<tr><td><xsl:value-of select="question" /></td><td><xsl:value-of select="answer" /></td></tr>
</xsl:for-each>
</tbody>
</table>

<div class="btn-group">
<a href="/{/output/page}/{@id}" class="btn btn-default">Again</a>
<a href="/{/output/page}" class="btn btn-default">Back</a>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1><xsl:value-of select="/output/layout/title/@page" /></h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="access_code" />
<xsl:apply-templates select="questionnaire" />
<xsl:apply-templates select="after" />
<xsl:apply-templates select="answers" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
