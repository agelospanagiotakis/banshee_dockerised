<?xml version="1.0" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../banshee/main.xslt" />

<!--
//
//  Edit template
//
//-->
<xsl:template match="edit">
<form method="post" action="/demos/ckeditor">
<textarea id="editor" name="editor" class="form-control"><p>This is a sample text.</p></textarea>

<div class="btn-group">
<input type="submit" name="save" value="Submit" class="btn btn-default" />
<a href="/demos" class="btn btn-default">Back</a>
</div>
</form>
</xsl:template>

<!--
//
//  Submit template
//
//-->
<xsl:template match="submit">
<h2>HTML code:</h2>
<div class="result"><xsl:value-of select="editor" /></div>

<h2>Rendered result:</h2>
<div class="result ck-content"><xsl:value-of select="editor" disable-output-escaping="yes" /></div>

<div class="btn-group">
<a href="/demos/ckeditor" class="btn btn-default">Back</a>
</div>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>CKEditor</h1>
<xsl:apply-templates select="edit" />
<xsl:apply-templates select="submit" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
