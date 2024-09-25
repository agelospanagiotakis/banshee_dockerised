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

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<xsl:variable name="now" select="appointments/@now" />

<h2>Appointments in the future</h2>
<table class="table table-striped table-hover table-condensed">
<thead>
<tr><th class="title">Title</th><th class="date">Begin</th><th class="date">End</th></tr>
</thead>
<tbody>
<xsl:for-each select="appointments/appointment[timestamp&gt;$now]">
<xsl:sort select="begin" order="ascending" />
<tr onclick="javascript:document.location='/{/output/page}/{@id}'">
<td><xsl:value-of select="title" /></td>
<td><xsl:value-of select="begin" /></td>
<td><xsl:value-of select="end" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>

<h2>Appointments in the past</h2>
<table class="table table-striped table-hover table-condensed">
<thead>
<tr><th class="title">Title</th><th class="date">Begin</th><th class="date">End</th></tr>
</thead>
<tbody>
<xsl:for-each select="appointments/appointment[not(timestamp&gt;$now)]">
<xsl:sort select="begin" order="descending" />
<tr onclick="javascript:document.location='/{/output/page}/{@id}'">
<td><xsl:value-of select="title" /></td>
<td><xsl:value-of select="begin" /></td>
<td><xsl:value-of select="end" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>

<div class="btn-group">
<a href="/{/output/page}/new" class="btn btn-default">New appointment</a>
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
<xsl:if test="@id">
<input type="hidden" name="id" value="{@id}" />
</xsl:if>
<label for="begin">Begin:</label>
<input type="text" id="begin" name="begin" value="{begin}" class="form-control datepicker" />
<label for="end">End (optional):</label>
<input type="text" id="end" name="end" value="{end}" class="form-control datepicker" />
<label for="short">Short description:</label>
<input type="text" id="short" name="title" value="{title}" class="form-control" />
<label for="editor">Long description:</label>
<textarea id="editor" name="content" class="form-control"><xsl:value-of select="content" /></textarea>

<div class="btn-group">
<input type="submit" name="submit_button" value="Save appointment" class="btn btn-default" />
<a href="/{/output/page}" class="btn btn-default">Cancel</a>
<xsl:if test="@id">
<input type="submit" name="submit_button" value="Delete appointment" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
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
<img src="/images/icons/agenda.png" class="title_icon" />
<h1>Agenda administration</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
