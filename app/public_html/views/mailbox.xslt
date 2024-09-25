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
//  Mailbox template
//
//-->
<xsl:template match="mailbox">
<div class="row">
<div class="col-sm-2">

<ul class="nav nav-pills folders">
<li><xsl:if test="/output/page/@url='/mailbox'"><xsl:attribute name="class">active</xsl:attribute></xsl:if><a href="/mailbox">Inbox <xsl:if test="@new>0"><span class="badge"><xsl:value-of select="@new" /></span></xsl:if></a></li>
<li><xsl:if test="/output/page/@url='/mailbox/sent'"><xsl:attribute name="class">active</xsl:attribute></xsl:if><a href="/mailbox/sent">Sent</a></li>
<li><xsl:if test="/output/page/@url='/mailbox/archive'"><xsl:attribute name="class">active</xsl:attribute></xsl:if><a href="/mailbox/archive">Archive</a></li>
</ul>

</div>
<div class="col-sm-10">

<table class="table table-striped table-hover table-condensed mailbox">
<thead>
<tr><th class="subject">Subject</th><th class="from"><xsl:value-of select="@column" /></th><th class="date">Date</th></tr>
</thead>
<tbody>
<xsl:for-each select="mail">
<tr class="{read}" onClick="javascript:document.location='/{/output/page}/{@id}'">
<td><xsl:value-of select="subject" /></td>
<td><xsl:value-of select="user" /></td>
<td><xsl:value-of select="timestamp" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>

<div class="btn-group">
<a href="/{/output/page}/new" class="btn btn-default">New mail</a>
</div>

</div>
</div>

</xsl:template>

<!--
//
//  Mail template
//
//-->
<xsl:template match="mail">
<form action="/{/output/page}{@folder}" method="post">
<input type="hidden" name="id" value="{@id}" />
<div class="panel panel-default">
<div class="panel-heading">
<table class="mailheader">
<tbody>
<tr><td>Subject:</td><td><xsl:value-of select="subject" /></td></tr>
<tr><td>From:</td><td><xsl:value-of select="from_user" /></td></tr>
<tr><td>To:</td><td><xsl:value-of select="to_user" /></td></tr>
<tr><td>Date:</td><td><xsl:value-of select="timestamp" /></td></tr>
</tbody>
</table>
</div>
<div class="panel-body"><xsl:value-of disable-output-escaping="yes" select="message" /></div>
</div>

<div class="btn-group">
<xsl:if test="@actions='yes' and from_user_id!=/output/user/@id">
<a href="/{/output/page}/reply/{@id}" class="btn btn-default">Reply</a>
<xsl:if test="archived='no'"><input type="submit" name="submit_button" value="Archive mail" class="btn btn-default" /></xsl:if>
</xsl:if>
<input type="submit" name="submit_button" value="Delete mail" class="btn btn-default" onClick="return confirm('DELETE: Are you sure?')" />
<a href="/{/output/page}{@folder}" class="btn btn-default">Back</a>
</div>
</form>
</xsl:template>

<!--
//
//  Write template
//
//-->
<xsl:template match="write">
<xsl:call-template name="show_messages" />
<form action="/{/output/page}" method="post">
<label for="to">To:</label>
<select name="to_user_id" class="form-control">
<xsl:for-each select="recipients/recipient">
<option value="{@id}">
<xsl:if test="@id=../../mail/to_user_id">
<xsl:attribute name="selected">selected</xsl:attribute>
</xsl:if>
<xsl:value-of select="." /></option>
</xsl:for-each>
</select>
<label for="subject">Subject:</label>
<input type="text" id="subject" name="subject" value="{mail/subject}" class="form-control" />
<label for="message">Message:</label>
<textarea id="message" name="message" class="form-control"><xsl:value-of select="mail/message" /></textarea>

<div class="btn-group">
<input type="submit" name="submit_button" value="Send mail" class="btn btn-default" />
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
<h1>Mailbox</h1>
<xsl:apply-templates select="mailbox" />
<xsl:apply-templates select="mail" />
<xsl:apply-templates select="write" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
