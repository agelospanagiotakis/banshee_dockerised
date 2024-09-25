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
//  Overview template
//
//-->
<xsl:template match="subscribe">
<p>Subscribe here to our newsletter.</p>
<xsl:call-template name="show_messages" />
<form action="/{/output/page}" method="post" class="newsletter">
<label for="email">E-mail address:</label>
<input type="text" id="email" name="email" class="form-control" />

<div class="btn-group">
<input type="submit" name="submit_button" value="Subscribe" class="btn btn-default" />
<input type="submit" name="submit_button" value="Unsubscribe" class="btn btn-default" />
</div>
</form>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Newsletter</h1>
<xsl:apply-templates select="subscribe" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
