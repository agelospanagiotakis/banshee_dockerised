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
<xsl:output method="html" doctype-system="about:legacy-compat" />

<xsl:template match="xslt_generated">
<p>This line is rendered by XSLT.<xsl:if test="content!=''"> Parameter: <xsl:value-of select="content" /></xsl:if></p>
</xsl:template>

</xsl:stylesheet>
