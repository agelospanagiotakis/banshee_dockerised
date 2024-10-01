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
<xsl:import href="../banshee/pagination.xslt" />

<!--
//
//  Order template
//
//-->
<xsl:template match="orders">
<xsl:if test="count(order)=0">
<p>There are no orders available.</p>
</xsl:if>
<ul class="list-group">
<xsl:for-each select="order">
<div class="row list-group-item">
<div class="col-sm-4">
<div>Order date: <xsl:value-of select="timestamp" /></div>
<h3>Shipping address</h3>
<div><xsl:value-of select="name" /></div>
<div><xsl:value-of select="address" /></div>
<div><xsl:value-of select="zipcode" />, <xsl:value-of select="city" /></div>
<div><xsl:value-of select="country" /></div>
</div>

<div class="col-sm-8">
<table class="table table-striped table-condensed confirm">
<thead>
<tr>
<th>Article</th>
<th>Price</th>
<th>Count</th>
</tr>
</thead>
<tbody>
<xsl:for-each select="articles/article">
<tr>
<td><a href="/webshop/{@id}"><xsl:value-of select="title" /></a></td>
<td><span class="currency"><xsl:value-of select="../@currency" disable-output-escaping="yes" /></span><xsl:value-of select="price" /></td>
<td><xsl:value-of select="quantity" /></td>
</tr>
</xsl:for-each>
</tbody>
<tfoot>
<tr>
<td>Total:</td>
<td><span class="currency"><xsl:value-of select="articles/@currency" disable-output-escaping="yes" /></span><xsl:value-of select="articles/@total" /></td>
<td><xsl:value-of select="articles/@count" /></td>
</tr>
</tfoot>
</table>
</div>
</div>
</xsl:for-each>
</ul>

<div class="right">
<xsl:apply-templates select="pagination" />
</div>
<div class="btn-group left">
<a href="/webshop" class="btn btn-default">Back</a>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<form action="/{/output/page}" method="post" class="type">
<select name="type" class="form-control" onChange="javascript:submit()">
<option value="0">Open orders</option>
<option value="1"><xsl:if test="orders/@closed='yes'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>Closed orders</option>
</select>
<input type="hidden" name="submit_button" value="type" />
</form>
<h1>Orders</h1>
<xsl:apply-templates select="orders" />
</xsl:template>

</xsl:stylesheet>
