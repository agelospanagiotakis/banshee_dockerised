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
//  Overview template
//
//-->
<xsl:template match="overview">
<xsl:for-each select="sections/section">
	<xsl:variable name="section_id" select="@id" />
	<h2><xsl:value-of select="." /></h2>
	<div class="panel-group">
	<xsl:for-each select="../../faqs/faq[section_id=$section_id]">
	<div class="panel panel-default">
	<div class="panel-heading" onClick="javascript:toggle_item({@id})"><xsl:value-of select="question" /></div>
	<div class="panel-body" id="faq{@id}"><xsl:value-of disable-output-escaping="yes" select="answer" /></div>
	</div>
	</xsl:for-each>
	</div>
</xsl:for-each>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Frequently Asked Questions</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
