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
//  Path
//
//-->
<xsl:template match="path">
<ul class="path">
<xsl:for-each select="dir">
<li><a href="/{/output/page}{@path}"><xsl:value-of select="." /></a></li>
</xsl:for-each>
</ul>
</xsl:template>

<!--
//
//  Structure
//
//-->
<xsl:template match="structure">
<xsl:param name="dir" />
<xsl:variable name="path"><xsl:value-of select="$dir" />/<xsl:value-of select="@name" /></xsl:variable>
<div style="margin-left:{@depth*25}px">
<xsl:if test="structure"><xsl:attribute name="class">leaf</xsl:attribute></xsl:if>
<div class="folder">
<xsl:if test="structure">
	<xsl:if test="@type='open'">
		<img src="/images/arrow1.png" class="node" onClick="javascript:toggle_trunk(this)" />
	</xsl:if>
	<xsl:if test="@type='closed'">
		<img src="/images/arrow0.png" class="node" onClick="javascript:toggle_trunk(this)" />
	</xsl:if>
</xsl:if>
<xsl:if test="not(structure)">
	<img src="/images/empty.png" class="node" />
</xsl:if>
<img src="/images/directory.png" class="node" /><a href="/{/output/page}{$path}/"><xsl:value-of select="@name" /></a>
</div>
</div>
<xsl:if test="structure">
	<div class="trunk {@type}">
	<xsl:apply-templates select="structure"><xsl:with-param name="dir" select="$path" /></xsl:apply-templates>
	</div>
</xsl:if>
</xsl:template>

<!--
//
//  List
//
//-->
<xsl:template match="list">
<table class="table table-striped table-hover list">
<thead>
<tr><th>Name</th><th>Modified</th><th>Size</th></tr>
</thead>
<tbody>
<xsl:for-each select="directory">
<tr class="directory" onClick="javascript:click_anchor(this)">
<td><a href="{link}/"><xsl:value-of select="name" /></a></td>
<td><xsl:value-of select="modified" /></td>
<td></td>
</tr>
</xsl:for-each>
<xsl:for-each select="file">
<tr class="file {ext}" onClick="javascript:click_anchor(this)">
<td><a href="{link}"><xsl:value-of select="name" /></a></td>
<td><xsl:value-of select="modified" /></td>
<td><xsl:value-of select="size" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>
</xsl:template>

<!--
//
//  Directory
//
//-->
<xsl:template match="directory">
<div class="row">
<div class="col-lg-3 col-md-4">
<div class="structure">
<div class="folder"><img src="/images/directory.png" class="node" /><a href="/{/output/page}/">Home</a></div>
<xsl:apply-templates select="structure" />
</div>
</div>
<div class="col-lg-9 col-md-8">
<xsl:apply-templates select="path" />
<xsl:apply-templates select="list" />
</div>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Download</h1>
<xsl:apply-templates select="directory" />
</xsl:template>

</xsl:stylesheet>
