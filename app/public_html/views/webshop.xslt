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
//  Webshop template
//
//-->
<xsl:template match="webshop">
<div class="row webshop-body">
<div class="col-sm-3">
<div class="list-group toggle">
<div class="list-group-item btn btn-default" onClick="javascript:show_categories()">Show categories</div>
</div>
<div class="list-group categories">
<xsl:for-each select="categories/category">
<a href="/{/output/page}/category/{@id}"><xsl:choose><xsl:when test="@id=../@current"><xsl:attribute name="class">list-group-item active</xsl:attribute></xsl:when><xsl:otherwise><xsl:attribute name="class">list-group-item</xsl:attribute></xsl:otherwise></xsl:choose><xsl:value-of select="." /></a>
</xsl:for-each>
</div>
</div>

<div class="col-sm-9">
<xsl:for-each select="articles/article">
<div class="panel panel-default">
<div class="panel-heading"><a href="/{/output/page}/{@id}"><xsl:value-of select="title" /></a> (<xsl:value-of select="article_nr" />)<span class="price"><span class="currency"><xsl:value-of select="../@currency" disable-output-escaping="yes" /></span><xsl:value-of select="price" /></span></div>
<div class="panel-body"><xsl:if test="image!=''"><a href="/{/output/page}/{@id}"><img src="{image}" alt="product" class="image small" /></a></xsl:if><xsl:value-of select="short_description" /></div>
</div>
</xsl:for-each>
</div>
</div>

<div class="webshop-footer">
<div class="btn-group left">
<a href="/{/output/page}/orders" class="btn btn-default">View orders</a>
</div>
<div class="right">
<xsl:apply-templates select="pagination" />
</div>
</div>
</xsl:template>

<!--
//
//  Article template
//
//-->
<xsl:template match="article">
<div class="panel panel-default">
<div class="panel-heading"><xsl:value-of select="title" /> - <xsl:value-of select="article_nr" /><span class="price"><span class="currency"><xsl:value-of select="@currency" disable-output-escaping="yes" /></span><xsl:value-of select="price" /></span></div>
<div class="panel-body">
<div class="info"><xsl:if test="image!=''"><img src="{image}" alt="product" class="image big" /></xsl:if><xsl:value-of select="long_description" /></div>
<div class="add"><input type="button" value="Add to cart" class="btn btn-xs btn-primary" onClick="javascript:add_to_cart({@id})" /></div>
</div>
</div>

<a href="/{/output/page}" class="btn btn-default">Back</a>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Webshop</h1>
<div class="row webshop-header">
<div class="col-xs-6"><div class="cart"><a href="/{/output/page}/cart"><span><xsl:value-of select="cart" /></span></a></div></div>
<div class="col-xs-6"><form action="/{/output/page}" method="post" class="search">
	<div class="input-group">
		<input type="text" id="search" name="search" value="{search}" placeholder="Search" class="form-control" />
		<xsl:if test="search!=''">
		<span class="input-group-btn"><input type="button" value="X" class="btn btn-default" onClick="javascript:$('input#search').val(''); $('form.search').submit();"/></span>
		</xsl:if>
	</div><!-- /input-group -->
	<input type="hidden" name="submit_button" value="search" />
</form></div>
</div>

<xsl:apply-templates select="webshop" />
<xsl:apply-templates select="article" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
