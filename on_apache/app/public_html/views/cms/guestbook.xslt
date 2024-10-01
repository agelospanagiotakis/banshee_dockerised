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
<xsl:template match="guestbook">
<table class="table table-striped table-condensed">
<thead>
<tr>
<th class="author"><a href="?order=author">Author</a></th>
<th class="message"><a href="?order=message">Message</a></th>
<th class="timestamp"><a href="?order=timestamp">Timestamp</a></th>
<th class="ip_address"><a href="?order=ip_address">IP address</a></th>
<th class="delete"></th>
</tr>
</thead>
<tbody>
<xsl:for-each select="item">
<tr>
<td><xsl:value-of select="author" /></td>
<td><xsl:value-of select="message" /></td>
<td><xsl:value-of select="timestamp" /></td>
<td><xsl:value-of select="ip_address" /></td>
<td><form action="/{/output/page}" method="post">
<input type="hidden" name="id" value="{@id}" />
<input type="submit" name="submit_button" value="delete" class="btn btn-xs btn-primary" onClick="javascript:return confirm('DELETE: Are you sure?')" />
</form></td>
</tr>
</xsl:for-each>
</tbody>
</table>
<xsl:apply-templates select="pagination" />

<div class="btn-group">
<a href="/cms" class="btn btn-default">Back</a>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<img src="/images/icons/guestbook.png" class="title_icon" />
<h1>Guestbook administration</h1>
<xsl:apply-templates select="guestbook" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
