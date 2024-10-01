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

<!--
//
//  Letters template
//
//-->
<xsl:template match="letters">
<div class="right">
<ul class="pagination">
<xsl:for-each select="letter">
	<li>
	<xsl:if test=".=../@selected"><xsl:attribute name="class">disabled</xsl:attribute></xsl:if>
	<a href="/{/output/page}/{.}"><xsl:value-of select="." /></a>
	</li>
</xsl:for-each>
</ul>
</div>
</xsl:template>

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<table class="table table-striped table-condensed">
<thead>
<tr><th>Word</th><th>Short description</th></tr>
</thead>
<tbody>
<xsl:for-each select="words/word">
	<tr><td class="word">
	<xsl:choose>
		<xsl:when test="long_description=''">
			<xsl:value-of select="word" />
		</xsl:when>
		<xsl:otherwise>
			<a href="/{/output/page}/{@id}"><xsl:value-of select="word" /></a>
		</xsl:otherwise>
	</xsl:choose>
	</td><td class="short"><xsl:value-of select="short_description" /></td></tr>
</xsl:for-each>
</tbody>
</table>

<xsl:apply-templates select="letters" />
</xsl:template>

<!--
//
//  keyword template
//
//-->
<xsl:template match="word">
<div class="panel panel-default">
<div class="panel-heading"><xsl:value-of select="word/word" /></div>
<div class="panel-body"><xsl:value-of disable-output-escaping="yes" select="word/long_description" /></div>
</div>

<xsl:apply-templates select="letters" />

<div class="btn-group left">
<a href="/{/output/page}/{letters/@selected}" class="btn btn-default">Back</a>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Dictionary</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="word" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
