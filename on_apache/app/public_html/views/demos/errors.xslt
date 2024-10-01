<?xml version="1.0" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../banshee/main.xslt" />

<xsl:template match="content">
<h1>Error messages</h1>
<p>This page demonstrates what (internal) errors and messages look like. Internal errors are only shown when the website is in debug mode and the view library has not been disabled. Otherwise, they are sent by e-mail to the webmaster.</p>

<div class="btn-group">
<a href="/demos" class="btn btn-default">Back</a>
</div>
</xsl:template>

</xsl:stylesheet>
