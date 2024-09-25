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
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="banshee/main.xslt" />

<!--
//
//  Links template
//
//-->
<xsl:template match="links">
<h2><xsl:value-of select="category" /></h2>
<xsl:if test="description!=''"><p><xsl:value-of select="description" /></p></xsl:if>
<ul class="links">
<xsl:for-each select="link">
<li><a href="{link}" target="_blank"><xsl:value-of select="text" /></a><xsl:if test="description!=''">: <xsl:value-of select="description" /></xsl:if></li>
</xsl:for-each>
</ul>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Links</h1>
<xsl:apply-templates select="links" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
