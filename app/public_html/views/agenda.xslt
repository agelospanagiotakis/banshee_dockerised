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
//  List template
//
//-->
<xsl:template match="list">
<table class="table table-striped list">
<thead>
<tr>
<th>Begin</th><th>End</th><th>Title</th>
</tr>
</thead>
<tbody>
<xsl:for-each select="appointment">
<tr>
<td><xsl:value-of select="begin" /></td>
<td><xsl:value-of select="end" /></td>
<td><xsl:value-of select="title" /></td>
</tr>
<tr>
<td colspan="3"><xsl:value-of disable-output-escaping="yes" select="content" /></td>
</tr>
</xsl:for-each>
</tbody>
</table>

<div class="btn-group">
<a href="/{/output/page}" class="btn btn-default">Back</a>
</div>
</xsl:template>

<!--
//
//  Month template
//
//-->
<xsl:template match="month">
<div class="row">
<div class="col-sm-4"><h2><xsl:value-of select="@title" /></h2></div>
<div class="col-sm-8"><div class="btn-group btn-responsive">
	<a href="/{/output/page}/list" class="btn btn-xs btn-primary">List view</a>
	<a href="/{/output/page}/{prev}" class="btn btn-xs btn-primary">Previous month</a>
	<a href="/{/output/page}/current" class="btn btn-xs btn-primary">Current month</a>
	<a href="/{/output/page}/{next}" class="btn btn-xs btn-primary">Next month</a>
</div></div>
</div>

<table class="month" cellspacing="0">
<thead>
<tr>
<xsl:for-each select="days_of_week/day">
<th><xsl:value-of select="." /></th>
</xsl:for-each>
</tr>
</thead>
<tbody>
<xsl:for-each select="week">
	<tr class="week">
	<xsl:for-each select="day">
		<td class="day dow{@dow}{@today}">
			<div class="nr"><xsl:value-of select="@nr" /></div>
			<xsl:for-each select="appointment">
				<div class="appointment"><a href="/{/output/page}/{@id}"><xsl:value-of select="." /></a></div>
			</xsl:for-each>
		</td>
	</xsl:for-each>
	</tr>
</xsl:for-each>
</tbody>
</table>
</xsl:template>

<!--
//
//  Appointment template
//
//-->
<xsl:template match="appointment">
<div class="appointment">
<h2><xsl:value-of select="title" /></h2>
<h3><span><xsl:value-of select="begin" /></span><xsl:if test="end!=''"><span><xsl:value-of select="end" /></span></xsl:if></h3>
<xsl:value-of disable-output-escaping="yes" select="content" />
</div>

<div class="btn-group">
<a href="/{/output/page}" class="btn btn-default">Back</a>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Agenda</h1>
<xsl:apply-templates select="list" />
<xsl:apply-templates select="month" />
<xsl:apply-templates select="appointment" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
