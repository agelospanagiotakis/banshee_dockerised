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
<table class="table table-striped table-hover table-condensed">
<thead>
<tr><th class="word">Word</th><th>Short description</th></tr>
</thead>
<tbody>
<xsl:for-each select="words/word">
	<tr onClick="javascript:document.location='/{/output/page}/{@id}'">
	<td><xsl:value-of select="word" /></td>
	<td><xsl:value-of select="short_description" /></td>
	</tr>
</xsl:for-each>
</tbody>
</table>

<div class="right">
<xsl:apply-templates select="pagination" />
</div>
<div class="btn-group left">
<a href="/{/output/page}/new" class="btn btn-default">New word</a>
<a href="/cms" class="btn btn-default">Back</a>
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
<form action="/{/output/page}" method="post">
<xsl:if test="@id">
<input type="hidden" name="id" value="{@id}" />
</xsl:if>
<label for="word">Word:</label>
<input type="text" id="word" name="word" value="{word}" class="form-control" />
<label for="short">Short description:</label>
<input type="text" id="short" name="short_description" value="{short_description}" class="form-control" />
<label for="editor">Long description:</label>
<textarea id="editor" name="long_description" class="form-control"><xsl:value-of select="long_description" /></textarea>

<div class="btn-group">
<input type="submit" name="submit_button" value="Save word" class="btn btn-default" />
<a href="/{/output/page}" class="btn btn-default">Cancel</a>
<xsl:if test="@id">
<input type="submit" name="submit_button" value="Delete word" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
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
<img src="/images/icons/dictionary.png" class="title_icon" />
<h1>Dictionary administration</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
