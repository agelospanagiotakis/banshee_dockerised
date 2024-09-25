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
<xsl:import href="banshee/pagination.xslt" />

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<div class="albums row">
<xsl:for-each select="albums/album">
<div class="album col-lg-3 col-md-6 col-xs-12">
	<div class="name"><xsl:value-of select="name" /></div>
	<div class="image"><a href="/{/output/page}/{@id}"><img src="/{/output/page}/image_{thumbnail}.{extension}" alt="thumbnail {thumbnail}" /></a></div>
	<div class="timestamp"><xsl:value-of select="timestamp" /></div>
	<div class="description"><xsl:value-of select="description" /></div>
</div>
</xsl:for-each>
</div>

<div class="right">
<xsl:apply-templates select="pagination" />
</div>
</xsl:template>

<!--
//
//  Photos template
//
//-->
<xsl:template match="photos">
<div class="photos row" id="gallery">
<xsl:for-each select="photo">
<div class="photo"><a href="/{/output/page}/image_{@id}.{extension}" title="{title}"><div class="box"><img src="/{/output/page}/thumbnail_{@id}.{extension}" alt="{title}" /></div></a></div>
</xsl:for-each>
</div>

<div class="info"><span><xsl:value-of select="@timestamp" /></span><span><xsl:value-of select="@info" /></span></div>

<div class="right">
<xsl:apply-templates select="pagination" />
</div>
<xsl:if test="@listed='yes'">
<div class="btn-group left">
<a href="/{/output/page}" class="btn btn-default">Back</a>
</div>
</xsl:if>
<div class="clear"></div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1><xsl:value-of select="/output/layout/title/@page" /></h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="photos" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
