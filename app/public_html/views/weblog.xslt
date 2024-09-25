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

<!--
//
//  Weblogs template
//
//-->
<xsl:template match="weblogs">
<xsl:apply-templates select="weblog" />
</xsl:template>

<!--
//
//  Weblog template
//
//-->
<xsl:template match="weblog">
<div class="weblog">

<div class="row weblog-header">
	<div class="col-sm-8"><h2><a href="/{/output/page}/{@id}"><xsl:value-of select="title" /></a></h2></div>
	<div class="col-sm-4"><xsl:value-of select="timestamp" /></div>
</div>

<div class="weblog-body"><xsl:value-of disable-output-escaping="yes" select="content" /></div>

<!-- Tags -->
<div class="row weblog-footer">
	<div class="col-xs-8 tags">
		Tags: <xsl:for-each select="tags/tag">
		<span class="tag"><a href="/{/output/page}/tag/{@id}"><xsl:value-of select="." /></a></span>
		</xsl:for-each>
	</div>
	<div class="col-xs-4 author">
		<div>by <xsl:value-of select="author" /></div>
		<xsl:if test="comment_count">
		<div><a href="/{/output/page}/{@id}"><span class="glyphicon glyphicon-comment" aria-hidden="true" /> Comments: <xsl:value-of select="comment_count" /></a></div>
		</xsl:if>
	</div>
</div>

<div class="social">
<a href="https://www.linkedin.com/shareArticle?mini=true&amp;url=https://{/output/website_url}/weblog/{@id}"><img src="/images/linkedin.png" /></a>
<a href="https://twitter.com/intent/tweet?text={title_link}&amp;url=https://{/output/website_url}/weblog/{@id}"><img src="/images/twitter.png" /></a>
</div>

</div>

<!-- Comments -->
<xsl:if test="comments">
<div class="comments">
<xsl:for-each select="comments/comment">
<div class="panel panel-default">
	<div class="panel-heading">
		<div class="row">
		<div class="col-sm-8"><xsl:value-of select="author" /></div>
		<div class="col-sm-4"><xsl:value-of select="timestamp" /></div>
		</div>
	</div>
	<div class="panel-body"><xsl:value-of disable-output-escaping="yes" select="content" /></div>
</div>
</xsl:for-each>

<!-- New comment form -->
<a name="new_comment" />
<form action="/{/output/page}#new_comment" method="post">
<input type="hidden" name="weblog_id" value="{@id}" />
<xsl:call-template name="show_messages" />
<label for="author">Name:</label>
<input type="text" id="author" name="author" value="{../comment/author}" class="form-control" />
<label for="content">Comment:</label>
<textarea id="content" name="content" class="form-control"><xsl:value-of select="../comment/content" /></textarea>

<div class="btn-group">
<input type="submit" value="Post" class="btn btn-default" />
<a href="/{/output/page}" class="btn btn-default">Back</a>
</div>
</form>

</div>
</xsl:if>
</xsl:template>

<!--
//
//  List template
//
//-->
<xsl:template match="list">
<h2><xsl:value-of select="@label" /></h2>
<ul class="tagged">
<xsl:for-each select="weblog">
<li><a href="/{/output/page}/{@id}"><xsl:value-of select="title" /></a> by <xsl:value-of select="author" />, <xsl:value-of select="timestamp" /></li>
</xsl:for-each>
</ul>
</xsl:template>

<!--
//
//  Sidebar template
//
//-->
<xsl:template match="sidebar">
<div class="well sidebar">
<p><a href="/{/output/page}">All articles</a></p>

<xsl:if test="count(tags/tag)>1">
All tags:
<ul>
<xsl:for-each select="tags/tag">
<li><a href="/{/output/page}/tag/{@id}"><xsl:value-of select="." /></a></li>
</xsl:for-each>
</ul>
</xsl:if>

<xsl:if test="count(years/year)>1">
Years:
<ul>
<xsl:for-each select="years/year">
<li><a href="/{/output/page}/period/{.}"><xsl:value-of select="." /></a></li>
</xsl:for-each>
</ul>
</xsl:if>

<xsl:if test="count(periods/period)>1">
Periods:
<ul>
<xsl:for-each select="periods/period">
<li><a href="/{/output/page}/period/{@link}"><xsl:value-of select="." /></a></li>
</xsl:for-each>
</ul>
</xsl:if>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Weblog</h1>
<xsl:if test="not(result)">
<div class="rsslink"><a href="/weblog.xml"><img src="/images/rss.png" alt="RSS" /></a></div>
<div class="row">
<div class="col-md-9">
<xsl:apply-templates select="weblogs" />
<xsl:apply-templates select="weblog" />
<xsl:apply-templates select="list" />
</div>
<div class="col-md-3">
<xsl:apply-templates select="sidebar" />
</div>
</div>
</xsl:if>
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
