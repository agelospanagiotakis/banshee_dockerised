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
<div class="albums">
<form action="/{/output/page}" method="post">
<div>Photo album: <select name="album" onChange="javascript:submit()" class="form-control albums">
<xsl:for-each select="albums/album">
<option value="{@id}"><xsl:if test="@id=../@current"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if><xsl:value-of select="." /></option>
</xsl:for-each>
</select></div>
<input type="hidden" name="submit_button" value="album" />
</form>
</div>

<ul id="sortable" class="photos">
<xsl:for-each select="photos/photo">
<li id="p{@id}"><a href="/{/output/page}/{@id}"><img src="/photo/thumbnail_{@id}.{extension}" class="preview overview_{overview}" /></a><p><xsl:value-of select="title" /></p></li>
</xsl:for-each>
</ul>

<xsl:call-template name="show_messages" />
<form action="/{/output/page}" method="post" enctype="multipart/form-data">
<table class="settings">
<tr><td>Photos:</td><td><div class="input-group">
<span class="input-group-btn"><label class="btn btn-default">
<input type="file" accept="image/*" name="photos[]" multiple="multiple" style="display:none" class="form-control" onChange="$('#upload-file-info').val(implode_filenames(this.files))" />
Browse</label></span>
<input type="text" id="upload-file-info" readonly="readonly" class="form-control" />
</div>
</td></tr>
<tr><td>Overview:</td><td><input type="checkbox" name="overview" /></td></tr>
<tr><td>Thumbnail mode:</td><td><select name="mode" class="form-control">
<xsl:for-each select="modes/mode">
<option value="{position() - 1}"><xsl:value-of select="." /></option>
</xsl:for-each>
</select></td></tr>
</table>

<div class="btn-group">
<input type="submit" name="submit_button" value="Upload photos" class="btn btn-default" />
<a href="/cms" class="btn btn-default">Back</a>
</div>
<div class="btn-group">
<a href="/cms/photo/album" class="btn btn-default">Photo albums</a>
<a href="/cms/photo/collection" class="btn btn-default">Collections</a>
</div>
</form>
</xsl:template>

<!--
//
//  Edit template
//
//-->
<xsl:template match="edit">
<xsl:call-template name="show_messages" />
<form action="/{/output/page}" method="post">
<input type="hidden" name="id" value="{photo/@id}" />
<input type="hidden" name="extension" value="{photo/extension}" />
<img src="/photo/thumbnail_{photo/@id}.{photo/extension}" class="preview_edit" />
<label for="title">Title:</label>
<input type="text" id="title" name="title" value="{photo/title}" class="form-control" />
<div><label for="overview">Overview:</label>
<input type="checkbox" id="overview" name="overview"><xsl:if test="photo/overview='yes'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if></input></div>
<label for="mode">Thumbnail mode:</label>
<select name="mode" class="form-control">
<xsl:for-each select="modes/mode">
<option value="{position() - 1}"><xsl:if test="position()-1=../../photo/thumbnail_mode"><xsl:attribute name="selected" value="selected" /></xsl:if><xsl:value-of select="." /></option>
</xsl:for-each>
</select>

<div class="btn-group">
<input type="submit" name="submit_button" value="Update photo" class="btn btn-default" />
<input type="submit" name="submit_button" value="Delete photo" class="btn btn-default" onClick="javascript:return confirm('DELETE: Are you sure?')" />
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
<img src="/images/icons/photo.png" class="title_icon" />
<h1>Photo administration</h1>
<xsl:apply-templates select="overview" />
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
