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

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<table class="table table-striped table-hover table-condensed">
<thead>
<tr><th class="name">Name</th></tr>
</thead>
<tbody>
<xsl:for-each select="collections/collection">
<tr onClick="javascript:document.location='/{/output/page}/{@id}'">
	<td><xsl:value-of select="name" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>

<div class="btn-group">
<a href="/{/output/page}/new" class="btn btn-default">New collection</a>
<a href="/cms/photo" class="btn btn-default">Back</a>
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
<xsl:if test="collection/@id">
<input type="hidden" name="id" value="{collection/@id}" />
</xsl:if>
<label for="name">Name:</label>
<input type="text" id="name" name="name" value="{collection/name}" class="form-control" />
<label>Albums:</label>
<div class="row">
<xsl:for-each select="collection/albums/album">
<div class="col-sm-3 col-xs-6"><input type="checkbox" name="albums[]" value="{@id}">
	<xsl:if test="@checked='yes'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
</input><xsl:value-of select="." /></div>
</xsl:for-each>
</div>

<div class="btn-group">
<input type="submit" name="submit_button" value="Save collection" class="btn btn-default" />
<a href="/{/output/page}" class="btn btn-default">Cancel</a>
<xsl:if test="collection/@id">
<input type="submit" name="submit_button" value="Delete collection" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
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
<img src="/images/icons/collection.png" class="title_icon" />
<h1>Photo collection administration</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
