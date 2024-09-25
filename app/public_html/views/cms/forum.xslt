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
//  Overview template
//
//-->
<xsl:template match="overview">
<table class="table table-striped table-hover">
<thead>
<tr>
<th>Topic</th>
<th>Author</th>
<th>Timestamp</th>
<th>Messages</th>
</tr>
</thead>
<tbody>
<xsl:for-each select="topics/topic">
<tr onClick="javascript:document.location='/{/output/page}/{@id}'">
<td><xsl:value-of select="subject" /></td>
<td><xsl:value-of select="author" /></td>
<td><xsl:value-of select="first" /></td>
<td><xsl:value-of select="messages" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>

<div class="right">
<xsl:apply-templates select="pagination" />
</div>

<div class="btn-group left">
<a href="/cms" class="btn btn-default">Back</a>
<a href="/{/output/page}/element" class="btn btn-default">Forum elements</a>
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
<input type="hidden" name="id" value="{topic/@id}" />
<label for="subject">Topic:</label>
<input id="subject" name="subject" value="{topic/subject}" class="form-control" />
<label for="forum">Forum:</label>
<select id="forum" name="forum_id" class="form-control">
<xsl:for-each select="forums/forum">
<option value="{@id}"><xsl:if test="@id=../../topic/forum_id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if><xsl:value-of select="." /></option>
</xsl:for-each>
</select>
<div><b>Sticky:</b> <input type="checkbox" id="sticky" name="sticky" class="boolean"><xsl:if test="topic/sticky='yes'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input></div>
<div><b>Closed:</b> <input type="checkbox" id="closed" name="closed" class="boolean"><xsl:if test="topic/closed='yes'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input></div>

<div class="btn-group">
<input type="submit" name="submit_button" value="Save topic" class="btn btn-default" />
<input type="submit" name="submit_button" value="Delete topic" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
<a href="/{/output/page}" class="btn btn-default">Cancel</a>
</div>
</form>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<img src="/images/icons/forum.png" class="title_icon" />
<h1>Forum administration</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
