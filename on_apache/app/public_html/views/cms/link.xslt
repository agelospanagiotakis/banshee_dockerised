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
<xsl:import href="../banshee/main.xslt" />
<xsl:import href="../banshee/pagination.xslt" />

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<table class="table table-condensed table-striped table-hover">
<thead>
<tr>
<th><a href="?order=text">Text</a></th>
<th><a href="?order=category">Category</a></th>
<th><a href="?order=link">Link</a></th>
</tr>
</thead>
<tbody>
<xsl:for-each select="links/link">
<tr class="click" onClick="javascript:document.location='/{/output/page}/{@id}'">
<td><xsl:value-of select="text" /></td>
<td><xsl:value-of select="category" /></td>
<td><xsl:value-of select="link" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>

<div class="right">
<xsl:apply-templates select="pagination" />
</div>

<div class="btn-group left">
<a href="/{/output/page}/new" class="btn btn-default">New link</a>
<a href="/{/output/page}/category" class="btn btn-default">Edit categories</a>
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
<form action="/{/output/page}" method="post">
<xsl:if test="link/@id">
<input type="hidden" name="id" value="{link/@id}" />
</xsl:if>

<label for="category">Category:</label>
<select id="category_id" name="category_id" class="form-control">
<xsl:for-each select="categories/category">
<option value="{@id}"><xsl:if test="@id=../../link/category_id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if><xsl:value-of select="." /></option>
</xsl:for-each>
</select>
<label for="text">Text:</label>
<input type="text" id="text" name="text" value="{link/text}" class="form-control" />
<label for="link">Link:</label>
<input type="text" id="link" name="link" value="{link/link}" class="form-control" />
<label for="description">Description:</label>
<input type="text" id="description" name="description" value="{link/description}" class="form-control" />

<div class="btn-group">
<input type="submit" name="submit_button" value="Save link" class="btn btn-default" />
<a href="/{/output/page}" class="btn btn-default">Cancel</a>
<xsl:if test="link/@id">
<input type="submit" name="submit_button" value="Delete link" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
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
<img src="/images/icons/links.png" class="title_icon" />
<h1>Link administration</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
