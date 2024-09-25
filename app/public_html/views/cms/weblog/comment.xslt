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
<xsl:import href="../../banshee/main.xslt" />
<xsl:import href="../../banshee/pagination.xslt" />

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<table class="table table-condensed table-striped table-hover">
<thead>
<tr>
<th>Weblog</th><th>Author</th><th>Timestamp</th><th>IP address</th>
</tr>
</thead>
<tbody>
<xsl:for-each select="comments/comment">
<tr class="click" onClick="javascript:document.location='/{/output/page}/{@id}'">
<td><xsl:value-of select="weblog" /></td>
<td><xsl:value-of select="author" /></td>
<td><xsl:value-of select="timestamp" /></td>
<td><xsl:value-of select="ip_address" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>

<div class="left btn-group">
<a href="/cms/weblog" class="btn btn-default">Back</a>
</div>

<div class="right">
<xsl:apply-templates select="pagination" />
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
<xsl:if test="comment/@id">
<input type="hidden" name="id" value="{comment/@id}" />
</xsl:if>

<label for="author">Author:</label>
<input type="text" id="author" name="author" value="{comment/author}" class="form-control" />
<label for="content">Content:</label>
<textarea id="content" name="content" class="form-control"><xsl:value-of select="comment/content" /></textarea>

<div class="btn-group">
<input type="submit" name="submit_button" value="Save comment" class="btn btn-default" />
<a href="/{/output/page}" class="btn btn-default">Cancel</a>
<xsl:if test="comment/@id">
<input type="submit" name="submit_button" value="Delete comment" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
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
<h1>Weblog comments</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
