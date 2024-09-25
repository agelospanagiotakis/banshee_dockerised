<?xml version="1.0" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../banshee/main.xslt" />

<xsl:template match="content">
<h1>Posting library</h1>
<xsl:call-template name="show_messages" />

<form action="/demos/posting" method="post" accept-charset="utf-8">
<textarea name="input" class="form-control"><xsl:value-of select="input" /></textarea><br />

<div class="btn-group">
<input type="submit" value="Submit text" class="btn btn-default" />
<a href="/demos" class="btn btn-default">Back</a>
</div>
</form>

<div class="well"><xsl:value-of select="output" /></div>
<div class="well"><xsl:value-of disable-output-escaping="yes" select="output" /></div>
</xsl:template>

</xsl:stylesheet>
