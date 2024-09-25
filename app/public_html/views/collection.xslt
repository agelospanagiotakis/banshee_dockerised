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
<ul>
<xsl:for-each select="collections/collection">
<li><a href="/{/output/page}/{@id}"><xsl:value-of select="name" /></a></li>
</xsl:for-each>
</ul>
</xsl:template>

<!--
//
//  Collection template
//
//-->
<xsl:template match="collection">
<xsl:for-each select="album">
<div class="album">
	<div class="image"><a href="/photo/{@id}"><img src="/photo/thumbnail_{photo_id}.{extension}" /></a></div>
	<div class="name"><a href="/photo/{@id}"><xsl:value-of select="name" /></a></div>
	<div class="description"><xsl:value-of select="description" /></div>
</div>
</xsl:for-each>
<div class="clear" />
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1><xsl:value-of select="title" /></h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="collection" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
