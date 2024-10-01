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
<form action="/{/output/page}" method="post" class="search">
<div class="input-group">
<input type="text" id="search" name="search" value="{@search}" placeholder="Search" class="form-control" />
<span class="input-group-btn"><input type="button" value="X" class="btn btn-default" onClick="javascript:$('input#search').val(''); $(this).parent().parent().parent().submit();" /></span>
</div>
<input type="hidden" name="submit_button" value="search" />
</form>

<table class="table table-condensed table-striped table-hover">
<thead>
<tr>
<th><a href="?order=article_nr">Article number</a></th>
<th><a href="?order=title">Title</a></th>
<th><a href="?order=category">Category</a></th>
<th><a href="?order=price">Price</a></th>
</tr>
</thead>
<tbody>
<xsl:for-each select="articles/article">
<tr onClick="javascript:document.location='/{/output/page}/{@id}'">
<td><xsl:value-of select="article_nr" /></td>
<td><xsl:value-of select="title" /></td>
<td><xsl:value-of select="category" /></td>
<td><span class="currency"><xsl:value-of select="../@currency" disable-output-escaping="yes" /></span><xsl:value-of select="price" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>

<div class="right">
<xsl:apply-templates select="pagination" />
</div>

<div class="left btn-group">
<a href="/{/output/page}/new" class="btn btn-default">New article</a>
<a href="/cms" class="btn btn-default">Back</a>
</div>
<div class="left btn-group">
<a href="/cms/webshop/category" class="btn btn-default">Categories</a>
</div>
</xsl:template>

<!--
//
//  Edit template
//
//-->
<xsl:template match="edit">
<xsl:call-template name="show_messages" />
<form action="/{/output/page}" method="post">
<xsl:if test="article/@id">
<input type="hidden" name="id" value="{article/@id}" />
</xsl:if>

<label for="article_nr">Article number:</label>
<input type="text" id="article_nr" name="article_nr" value="{article/article_nr}" class="form-control" />
<label for="category">Category</label>
<select name="shop_category_id" class="form-control">
<xsl:for-each select="categories/category">
<option value="{@id}"><xsl:if test="@id=../../article/shop_category_id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if><xsl:value-of select="." /></option>
</xsl:for-each>
</select>
<label for="title">Title:</label>
<input type="text" id="title" name="title" value="{article/title}" class="form-control" />
<label for="short_description">Short descrtiption:</label>
<textarea id="short_description" name="short_description" class="form-control"><xsl:value-of select="article/short_description" /></textarea>
<label for="long_description">Long description:</label>
<textarea id="long_description" name="long_description" class="form-control"><xsl:value-of select="article/long_description" /></textarea>
<label for="image">Image:</label>
<input type="text" id="image" name="image" value="{article/image}" class="form-control" />
<label for="price">Price:</label>
<div class="input-group">
<span class="input-group-addon"><xsl:value-of disable-output-escaping="yes" select="@currency" /></span>
<input type="text" id="price" name="price" value="{article/price}" class="form-control" />
</div>
<label for="visible">Visible:</label>
<input type="checkbox" id="visible" name="visible"><xsl:if test="article/visible='yes'"><xsl:attribute name="checked">checked></xsl:attribute></xsl:if></input>

<div class="btn-group">
<input type="submit" name="submit_button" value="Save article" class="btn btn-default" />
<a href="/{/output/page}" class="btn btn-default">Cancel</a>
<xsl:if test="article/@id">
<input type="submit" name="submit_button" value="Delete article" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
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
<img src="/images/icons/articles.png" class="title_icon" />
<h1>Article administration</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
