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
<xsl:import href="main.xslt" />
<xsl:import href="alphabetize.xslt" />
<xsl:import href="pagination.xslt" />

<!--
//
//  Overview template
//
//-->
<xsl:template match="overview">
<xsl:if test="search">
<form action="" method="post" class="search">
<div class="search">
<div>
<xsl:if test="search!=''"><xsl:attribute name="class">input-group</xsl:attribute></xsl:if>
<input type="text" id="search" name="search" value="{search}" placeholder="Search" class="form-control" />
<xsl:if test="search!=''">
<span class="input-group-btn"><input type="button" name="submit_button" value="X" class="btn btn-default reset" onClick="$('input#search').val(''); $('form.search').submit();" /></span>
</xsl:if>
</div>
</div>
<input type="hidden" name="submit_button" value="Search" />
</form>
</xsl:if>

<table class="{@class}">
<thead>
<tr>
<xsl:for-each select="labels/label">
	<th class="{@name}">
	<xsl:if test="../@order='yes'"><a href="?order={@name}"><xsl:value-of select="." /></a></xsl:if>
	<xsl:if test="../@order='no'"><xsl:value-of select="." /></xsl:if>
	</th>
</xsl:for-each>
<xsl:if test="items/@sortable='yes'"><th class="sort"></th></xsl:if>
</tr>
</thead>
<tbody>
<xsl:for-each select="items/item">
<tr number="{@id}" class="click" onClick="javascript:document.location='/{/output/page}/{@id}'">
<xsl:for-each select="value">
	<td><xsl:value-of select="." /></td>
</xsl:for-each>
<xsl:if test="../@sortable='yes'"><td class="sort"><span class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></span></td></xsl:if>
</tr>
</xsl:for-each>
</tbody>
</table>

<div class="right">
<xsl:apply-templates select="alphabetize" />
<xsl:apply-templates select="pagination" />
</div>

<div class="left btn-group">
<xsl:if test="@allow_create='yes'">
<a href="/{/output/page}/new" class="new btn btn-default">New <xsl:value-of select="labels/@name" /></a>
</xsl:if>
<xsl:if test="../back">
<a href="/{../back}" class="back btn btn-default">Back</a>
</xsl:if>
</div>

<div class="clear"></div>
</xsl:template>

<!--
//
//  Edit template
//
//-->
<xsl:template match="edit">
<xsl:call-template name="show_messages" />
<form action="/{/output/page}" method="post" enctype="multipart/form-data">
<xsl:if test="form/@id">
<input type="hidden" name="id" value="{form/@id}" />
</xsl:if>

<xsl:for-each select="form/element">
<label for="{@name}"><xsl:value-of select="label" />:</label>
<xsl:choose>
	<!-- Blob -->
	<xsl:when test="@type='blob'">
		<input type="file" id="{@name}" name="{@name}[]" multiple="multiple" />
	</xsl:when>
	<!-- Boolean -->
	<xsl:when test="@type='boolean'">
		<input type="checkbox" id="{@name}" name="{@name}"><xsl:if test="value='yes'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input>
	</xsl:when>
	<!-- Date -->
	<xsl:when test="@type='date'">
		<input type="text" id="{@name}" name="{@name}" value="{value}" class="form-control datepicker" />
	</xsl:when>
	<!-- Enumerate -->
	<xsl:when test="@type='enum' or @type='foreignkey'">
		<select id="{@name}" name="{@name}" class="form-control">
		<xsl:for-each select="options/option">
		<option value="{@value}">
			<xsl:if test="@value=../../value"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
			<xsl:value-of select="." />
		</option>
		</xsl:for-each>
		</select>
	</xsl:when>
	<!-- Text -->
	<xsl:when test="@type='text' or @type='ckeditor'">
		<textarea id="{@name}" name="{@name}" class="form-control">
			<xsl:if test="placeholder"><xsl:attribute name="placeholder"><xsl:value-of select="placeholder" /></xsl:attribute></xsl:if>
			<xsl:if test="@type='ckeditor'">
				<xsl:attribute name="id">editor</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="value" />
		</textarea>
	</xsl:when>
	<!-- Timestamp -->
	<xsl:when test="@type='timestamp'">
		<input type="text" id="{@name}" name="{@name}" value="{value}" class="form-control datetimepicker" />
	</xsl:when>
	<!-- Other -->
	<xsl:otherwise>
		<input type="text" id="{@name}" name="{@name}" value="{value}" class="form-control">
			<xsl:if test="placeholder"><xsl:attribute name="placeholder"><xsl:value-of select="placeholder" /></xsl:attribute></xsl:if>
		</input>
	</xsl:otherwise>
</xsl:choose>
</xsl:for-each>

<div class="btn-group">
<input type="submit" name="submit_button" value="Save {form/@name}" class="save btn btn-default" />
<a href="/{/output/page}" class="cancel btn btn-default">Cancel</a>
<xsl:if test="form/@id and form/@allow_delete='yes'">
<input type="submit" name="submit_button" value="Delete {form/@name}" class="delete btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
</xsl:if>
</div>
</form>
</xsl:template>

<!--
//
//  Tablemanager template
//
//-->
<xsl:template match="tablemanager">
<xsl:if test="icon"><img src="/images/icons/{icon}" class="title_icon" /></xsl:if>
<h1><xsl:value-of select="name" /> administration</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
