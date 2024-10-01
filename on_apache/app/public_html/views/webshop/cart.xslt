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
//  cart template
//
//-->
<xsl:template match="cart">
<table class="table table-striped table-condensed">
<thead>
<tr>
<th>Article</th>
<th>Article number</th>
<th>Price</th>
<th colspan="2">Count</th>
</tr>
</thead>
<tbody>
<xsl:for-each select="article">
<tr>
<td><a href="/webshop/{@id}"><xsl:value-of select="title" /></a></td>
<td><xsl:value-of select="article_nr" /></td>
<td><span class="currency"><xsl:value-of select="../@currency" disable-output-escaping="yes" /></span><xsl:value-of select="price" /></td>
<td><xsl:value-of select="quantity" /></td>
<td><form action="/{/output/page}" method="post"><input type="hidden" name="id" value="{@id}" /><input type="submit" name="submit_button" value="+" class="btn btn-default" /><input type="submit" name="submit_button" value="-" class="btn btn-default" /></form></td>
</tr>
</xsl:for-each>
</tbody>
<tfoot>
<tr>
<td>Total:</td>
<td></td>
<td><span class="currency"><xsl:value-of select="@currency" disable-output-escaping="yes" /></span><xsl:value-of select="@total" /></td>
<td><xsl:value-of select="@quantity" /></td>
<td></td>
</tr>
</tfoot>
</table>

<xsl:if test="count(article)>0">
<div class="btn-group right">
<a href="/webshop/checkout" class="btn btn-default">Proceed to checkout</a>
</div>
</xsl:if>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Shopping cart</h1>
<xsl:if test="cart/article">
<xsl:apply-templates select="cart" />
</xsl:if>
<xsl:if test="not(cart/article)">
<p>Your shopping cart is empty.</p>
</xsl:if>
<xsl:apply-templates select="result" />

<div class="btn-group left">
<a href="/webshop" class="btn btn-default">Back</a>
</div>
</xsl:template>

</xsl:stylesheet>
