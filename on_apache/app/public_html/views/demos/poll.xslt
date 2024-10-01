<?xml version="1.0" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../banshee/main.xslt" />
<xsl:import href="../banshee/poll.xslt" />

<xsl:template match="content">
<h1>Poll</h1>
<xsl:apply-templates select="active_poll" />
<p>This page contains a poll demonstration. If you haven't voted yet, try it now.</p>

<div class="btn-group">
<a href="/demos" class="btn btn-default">Back</a>
</div>
</xsl:template>

</xsl:stylesheet>
