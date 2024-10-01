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
//  News item template
//
//-->
<xsl:template match="news">
<div class="panel panel-default">
<div class="panel-heading">
<div class="row">
<div class="col-sm-6"><xsl:value-of select="title" /></div>
<div class="col-sm-6"><xsl:value-of select="timestamp" /></div>
</div>
</div>
<div class="panel-body">
<xsl:value-of disable-output-escaping="yes" select="content" />
</div>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>News</h1>
<div class="rsslink"><a href="/news.xml"><img src="/images/rss.png" /></a></div>
<xsl:apply-templates select="news" />
<xsl:apply-templates select="pagination" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
