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
<xsl:import href="banshee/splitform.xslt" />


<!--
//
//  Layout templates
//
//-->
<xsl:template name="splitform_header">
<h1>Register</h1>
</xsl:template>

<xsl:template name="splitform_footer">
</xsl:template>

<!--
//
//  Invitation form template
//
//-->
<xsl:template match="splitform/invitation">
<p>If you've received an invitation code to join an existing organisation, enter it here. If you don't specify an invitation code, a new organisation will be created and you will become its maintainer.</p>
<label for="code">Invitation code (optional):</label>
<input type="text" name="invitation" value="{invitation}" class="form-control" />
</xsl:template>

<!--
//
//  E-mail form template
//
//-->
<xsl:template match="splitform/email">
<label for="email">E-mail address:</label>
<input type="input" id="email" name="email" value="{email}" class="form-control" />
</xsl:template>

<!--
//
//  Code form template
//
//-->
<xsl:template match="splitform/code">
<p>An e-mail with a verification code has been sent to your e-mail address.</p>
<label for="code">Verification code:</label>
<input type="text" name="code" value="{code}" class="form-control" />
</xsl:template>

<!--
//
//  Account form template
//
//-->
<xsl:template match="splitform/account">
<label for="fullname">Full name:</label>
<input type="text" id="fullname" name="fullname" value="{fullname}" class="form-control" />
<label for="username">Username:</label>
<input type="text" id="username" name="username" value="{username}" class="form-control" style="text-transform:lowercase" />
<label for="password">Password:</label>
<input type="password" id="password" name="password" class="form-control" />
<xsl:if test="ask_organisation='yes'">
<label for="organisation">Organisation:</label>
<input type="text" id="organisation" name="organisation" value="{organisation}" class="form-control" />
</xsl:if>
</xsl:template>

<!--
//
//  Authenticator form template
//
//-->
<xsl:template match="splitform/authenticator">
<label for="secret">Authenticator secret:</label>
<div class="input-group">
<input type="text" id="secret" name="authenticator_secret" value="{authenticator_secret}" class="form-control" style="text-transform:uppercase" />
<span class="input-group-btn"><input type="button" value="Generate" class="btn btn-default" onClick="javascript:set_authenticator_code()" /></span>
</div>
</xsl:template>

<!--
//
//  Process template
//
//-->
<xsl:template match="submit">
<xsl:call-template name="splitform_header" />
<xsl:call-template name="progressbar" />
<p>Your account has been created. You can now log in.</p>
<xsl:call-template name="redirect"><xsl:with-param name="url" /></xsl:call-template>
</xsl:template>

</xsl:stylesheet>
