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

<xsl:template match="alphabetize">
<ul class="pagination pagination-sm">
<xsl:for-each select="char">
<xsl:choose>
	<xsl:when test="@link=../@char">
		<li class="disabled"><a href="#"><xsl:value-of select="." /></a></li>
	</xsl:when>
	<xsl:otherwise>
		<li><a href="{/output/page/@url}?char={@link}"><xsl:value-of select="." /></a></li>
	</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</ul>
<div style="clear:both" />
</xsl:template>

</xsl:stylesheet>
