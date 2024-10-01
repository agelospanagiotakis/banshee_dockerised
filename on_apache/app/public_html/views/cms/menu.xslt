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
//  Template template
//
//-->
<xsl:template match="branch">
<ul>
<xsl:for-each select="item">
<li><input type="text" value="{text}" placeholder="Text" class="form-control" /><input type="text" value="{link}" placeholder="Link" class="form-control" />
<xsl:apply-templates select="branch" /></li>
</xsl:for-each>
</ul>
</xsl:template>

<!--
//
//  Edit template
//
//-->
<xsl:template match="edit">
<xsl:call-template name="show_messages" />

<form action="/{/output/page}" method="post">
<xsl:apply-templates select="branch" />
<div class="btn-group">
<input type="submit" name="submit_button" value="Update" class="btn btn-default" />
<a href="/cms" class="btn btn-default">Back</a>
</div>
</form>

<h2>Available static pages<button class="btn btn-default btn-xs" onClick="$('div.pages').toggle()">+</button></h2>
<div class="row pages">
<xsl:for-each select="pages/page">
<div class="col-xs-4"><div class="page" onClick="page_click(this)"><div><xsl:value-of select="." /></div><div><xsl:value-of select="@url" /></div></div></div>
</xsl:for-each>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<img src="/images/icons/menu.png" class="title_icon" />
<h1>Menu administration</h1>
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
