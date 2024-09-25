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
//  Forums template
//
//-->
<xsl:template match="forums">
<div class="forums row">
<xsl:for-each select="forum">
<xsl:if test="section!=''">
<div class="col-xs-12 section"><xsl:value-of select="section" /></div>
</xsl:if>
<div class="col-xs-12 title" onclick="javascript:click_anchor(this)">
<xsl:if test="icon!=''"><img src="{icon}" class="icon" /></xsl:if>
<a href="/{/output/page}/{@id}"><xsl:value-of select="title" /></a>
<span class="unread"><xsl:if test="unread>0">(<xsl:value-of select="unread" /> unread)</xsl:if></span>
</div>
<div class="col-xs-10 description"><xsl:value-of select="description" /></div>
<div class="col-xs-2 topics"><xsl:value-of select="topics " /> topics</div>
</xsl:for-each>
</div>
</xsl:template>

<!--
//
//  Forum template
//
//-->
<xsl:template match="forum">
<div class="forum">
<h2>[<xsl:value-of select="section" />] <xsl:value-of select="title" /></h2>
<table class="table table-striped table-hover topics">
<thead>
<tr><th>Topic</th><th>Author</th><th>Messages</th><th>Last message</th></tr>
</thead>
<tbody>
<xsl:for-each select="topics/topic">
	<tr onclick="javascript:click_anchor(this)"><xsl:if test="sticky='yes'"><xsl:attribute name="class">sticky</xsl:attribute></xsl:if>
	<td>
		<a href="/{/output/page}/topic/{@id}"><xsl:value-of select="subject" /></a>
		<xsl:if test="unread='yes'"><span class="unread">(unread posts)</span></xsl:if>
		<xsl:if test="closed='yes'"><img src="/images/lock.png" class="icon" /></xsl:if>
		<xsl:if test="sticky='yes'"><img src="/images/sticky.png" class="icon" /></xsl:if>
	</td>
	<td><xsl:value-of select="starter" /></td>
	<td><xsl:value-of select="messages" /></td>
	<td><xsl:value-of select="timestamp" /></td>
	</tr>
</xsl:for-each>
</tbody>
</table>
</div>

<div class="right">
<xsl:apply-templates select="pagination" />
</div>

<form action="/{/output/page}/{@id}" method="post">
<input type="hidden" name="forum_id" value="{@id}" />
<div class="btn-group left">
<a href="/{/output/page}/{@id}/new" class="btn btn-default">New topic</a>
<a href="/{/output/page}" class="btn btn-default">Back</a>
</div>
<xsl:if test="/output/user and count(topics/topic[unread='yes'])>0">
<div class="btn-group left">
<input type="submit" name="submit_button" value="Mark forum as read" class="btn btn-default" />
</div>
</xsl:if>
</form>
</xsl:template>

<!--
//
//  Topic template
//
//-->
<xsl:template match="topic">
<div class="topic">
<h3><a href="/{/output/page}/{@forum_id}">[<xsl:value-of select="section" />] <xsl:value-of select="title" /></a></h3>
<div class="row">
<div class="col-sm-8"><h2><xsl:value-of select="subject" /></h2></div>
<div class="col-sm-4 align_right"><xsl:apply-templates select="../pagination" /></div>
</div>
<xsl:for-each select="message">
	<a name="{@id}" />
	<div class="panel panel-default">
	<div class="panel-heading">
		<div class="row">
		<div class="col-xs-6"><span class="{usertype}"><xsl:if test="user_id!=''"><xsl:attribute name="onClick">document.location='/account/<xsl:value-of select="user_id" />'</xsl:attribute></xsl:if><xsl:value-of select="author" /></span><xsl:if test="unread='yes'"><span class="unread">(unread post)</span></xsl:if></div>
		<div class="col-sm-6"><xsl:value-of select="timestamp" /></div>
		</div>
	</div>
	<div class="panel-body">
		<xsl:if test="avatar!=''"><img src="{avatar}" class="avatar" /></xsl:if>
		<div class="message"><xsl:value-of disable-output-escaping="yes" select="content" /></div>
		<xsl:if test="edit='yes'"><div class="edit"><input type="button" value="Edit" class="btn btn-default btn-xs" onClick="javascript:edit_message(this, {@id})" /></div></xsl:if>
		<xsl:if test="signature!=''"><div class="signature"><xsl:value-of select="signature" disable-output-escaping="yes" /></div></xsl:if>
	</div>
	</div>
</xsl:for-each>

<div class="panel panel-default preview">
	<div class="panel-heading">Message preview</div>
	<div class="panel-body"></div>
</div>

<xsl:call-template name="show_messages" />
<a name="response" />
<form action="/{/output/page}/topic/{@id}#response" method="post" class="new_response">
<xsl:if test="@closed='no'">
<input type="hidden" name="forum_topic_id" value="{@id}" />
<xsl:if test="not(/output/user)">
<label for="username">Name:</label>
<input type="text" id="username" name="username" value="{response/username}" class="form-control" />
</xsl:if>
<label for="content">Message:</label>
<textarea id="content" name="content" class="form-control"><xsl:value-of select="response/content" /></textarea>
<xsl:call-template name="smilies" />
<xsl:call-template name="bbcodes" />
</xsl:if>

<div class="btn-group">
<xsl:if test="@closed='no'">
<input type="button" value="Preview response" class="btn btn-default" onClick="javascript:preview_message(this)" />
<input type="submit" name="submit_button" value="Post response" class="btn btn-default" />
</xsl:if>
<a href="/{/output/page}/{@forum_id}" class="btn btn-default">Back</a>
<xsl:if test="@moderator='yes'">
<a href="/cms/forum/{@id}" class="btn btn-default">Edit topic (CMS)</a>
</xsl:if>
</div>
<div class="right">
<xsl:apply-templates select="../pagination" />
</div>
</form>
</div>
</xsl:template>

<!--
//
//  New topic template
//
//-->
<xsl:template match="newtopic">
<xsl:call-template name="show_messages" />
<form action="/{/output/page}" method="post" class="new_topic">
<input type="hidden" name="forum_id" value="{forum_id}" />
<xsl:if test="not(/output/user)">
<label for="username">Name:</label>
<input type="text" id="username" name="username" value="{username}" class="form-control" />
</xsl:if>
<label for="subject">Topic:</label>
<input type="text" id="subject" name="subject" value="{subject}" class="form-control" />
<label for="content">Message:</label>
<textarea id="content" name="content" class="form-control"><xsl:value-of select="content" /></textarea>
<xsl:call-template name="smilies" />

<div class="btn-group">
<input type="submit" name="submit_button" value="Create topic" class="btn btn-default" />
<a href="/{/output/page}/{forum_id}" class="btn btn-default">Back</a>
</div>
</form>

<xsl:call-template name="bbcodes" />
</xsl:template>

<!--
//
//  Smilies template
//
//-->
<xsl:template name="smilies">
<div class="smilies">
<xsl:for-each select="../smilies/smiley">
<img src="/images/smilies/{.}" onClick="show_smiley('{@text}')" />
</xsl:for-each>
</div>
</xsl:template>

<!--
//
//  BB-codes template
//
//-->
<xsl:template name="bbcodes">
<div id="help">
<p>The following BB-codes are available in a message:</p>
<ul class="bbcodes">
<li><b>[b]</b>Bold text<b>[/b]</b></li>
<li><b>[center]</b>Center text or imagen<b>[/center]</b></li>
<li><b>[color=</b>color name or #RGB code<b>]</b>Colored text<b>[/color]</b></li>
<li><b>[i]</b>Italic text<b>[/i]</b></li>
<li><b>[img]</b>Link to image<b>[/img]</b></li>
<li><b>[right]</b>Align text or image right<b>[/right]</b></li>
<li><b>[s]</b>Strike-through text<b>[/s]</b></li>
<li><b>[size=</b>pixelsize<b>]</b>Big or small text<b>[/size]</b></li>
<li><b>[u]</b>Underlined text<b>[/u]</b></li>
<li><b>[url]</b>Link to website<b>[/url]</b></li>
<li><b>[url=</b>link to website<b>]</b>Link text<b>[/url]</b></li>
</ul>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Forum</h1>
<xsl:apply-templates select="forums" />
<xsl:apply-templates select="forum" />
<xsl:apply-templates select="topic" />
<xsl:apply-templates select="newtopic" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
