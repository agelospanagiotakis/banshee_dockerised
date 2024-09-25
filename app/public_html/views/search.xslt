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
//  Content template
//
//-->
<xsl:template match="content">
<h1>Search</h1>
<div class="well">
<form action="/{/output/page}" method="post">
<div class="input-group">
	<input type="text" id="query" name="query" value="{query}" class="form-control" />
	<span class="input-group-btn">
		<input type="submit" name="submit_button" value="Search" class="btn btn-default" />
	</span>
</div>

<div class="sections">Sections:
<xsl:for-each select="sections/section">
<span><input type="checkbox" name="{.}"><xsl:if test="@checked='yes'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input> <xsl:value-of select="@label" /></span>
</xsl:for-each>
</div>
</form>
</div>

<xsl:if test="result">
<div class="error"><xsl:value-of select="result" /></div>
</xsl:if>

<xsl:for-each select="section">
	<h2><xsl:value-of select="@label" /></h2>
	<ul class="pagination">
	<xsl:for-each select="hit">
		<li>
		<div class="link"><a href="{url}"><xsl:value-of select="text" /></a></div>
		<div class="preview"><xsl:value-of select="content" /></div>
		</li>
	</xsl:for-each>
	</ul>
</xsl:for-each>
</xsl:template>

</xsl:stylesheet>
