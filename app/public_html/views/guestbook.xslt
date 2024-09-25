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
<xsl:import href="banshee/pagination.xslt" />

<!--
//
//  Guestbook template
//
//-->
<xsl:template match="guestbook">
<xsl:for-each select="item">
<div class="panel panel-default">
<div class="panel-heading">
<div class="row">
<div class="col-sm-6"><xsl:value-of select="author" /></div>
<div class="col-sm-6"><xsl:value-of select="timestamp" /></div>
</div>
</div>
<div class="panel-body">
<xsl:value-of disable-output-escaping="yes" select="message" />
</div>
</div>
</xsl:for-each>
<xsl:apply-templates select="pagination" />

<xsl:if test="@skip_sign_link='no'">
<a name="sign" />
</xsl:if>
<form action="/{/output/page}#sign" method="post">
<xsl:call-template name="show_messages" />
<label for="name">Name:</label>
<input type="input" id="name" name="author" value="{../author}" class="form-control" />
<label for="message">Message:</label>
<textarea id="message" name="message" class="form-control"><xsl:value-of select="../message" /></textarea>
<div class="btn-group">
<input type="submit" name="submit_button" value="Sign guestbook" class="btn btn-default" />
</div>
</form>
</xsl:template>

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Guestbook</h1>
<xsl:apply-templates select="guestbook" />
<xsl:apply-templates select="result" />
</xsl:template>

</xsl:stylesheet>
