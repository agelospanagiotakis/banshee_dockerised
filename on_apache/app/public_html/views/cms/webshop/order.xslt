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
<xsl:import href="../../banshee/main.xslt" />
<xsl:import href="../../banshee/pagination.xslt" />

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<form action="/{/output/page}" method="post" class="type">
<select name="type" class="form-control" onChange="javascript:submit()">
<option value="0">Open orders</option>
<option value="1"><xsl:if test="orders/@closed='yes'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>Closed orders</option>
</select>
<input type="hidden" name="submit_button" value="type" />
</form>

<table class="table table-condensed table-striped table-hover overview">
<thead>
<th>ID</th><th>Customer</th><th>Timetamp</th><th>Amount</th><th>Articles</th>
</thead>
<tbody>
<xsl:for-each select="orders/order">
<tr onClick="javascript:document.location='/{/output/page}/{@id}'">
<td><xsl:value-of select="@id" /></td>
<td><xsl:value-of select="fullname" /></td>
<td><xsl:value-of select="timestamp" /></td>
<td><span class="currency"><xsl:value-of select="../@currency" disable-output-escaping="yes" /></span><xsl:value-of select="amount" /></td>
<td><xsl:value-of select="articles" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>

<div class="right">
<xsl:apply-templates select="pagination" />
</div>
<div class="btn-group left">
<a href="/cms" class="btn btn-default">Back</a>
</div>
</xsl:template>

<!--
//
//  Edit template
//
//-->
<xsl:template match="edit">
<xsl:call-template name="show_messages" />

<div class="row">
<div class="col-sm-4">
<div>Order ID: <xsl:value-of select="order/@id" /></div>
<div>Order date: <xsl:value-of select="order/timestamp" /></div>
<div>Closed: <xsl:value-of select="order/closed" /></div>
<h3>Shipping address</h3>
<div><xsl:value-of select="order/name" /></div>
<div><xsl:value-of select="order/address" /></div>
<div><xsl:value-of select="order/zipcode" />, <xsl:value-of select="order/city" /></div>
<div><xsl:value-of select="order/country" /></div>
<div><a href="mailto:{order/email}"><xsl:value-of select="order/email" /></a></div>
</div>

<div class="col-sm-8">
<table class="table table-striped table-condensed articles">
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
<td><xsl:value-of select="articles/@quantity" /></td>
</tr>
</tfoot>
</table>
</div>
</div>

<form action="/{/output/page}" method="post">
<input type="hidden" name="id" value="{order/@id}" />
<div class="btn-group">
<a href="/{/output/page}" class="btn btn-default">Back</a>
<input type="submit" name="submit_button" value="Delete order" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
<xsl:if test="order/closed='no'">
<input type="submit" name="submit_button" value="Close order" class="btn btn-default" onClick="javascript:return confirm('CLOSE: Are you sure?')" />
</xsl:if>
</div>
</form>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<img src="/images/icons/orders.png" class="title_icon" />
<h1>Webshop orders</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
