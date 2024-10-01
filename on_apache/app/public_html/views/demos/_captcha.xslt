<?xml version="1.0" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../banshee/main.xslt" />

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Captcha demo</h1>
<xsl:call-template name="show_messages" />
<p><img src="/captcha.png" /></p>
<form action="/demos/captcha" method="post">
<label for="code">Enter captcha code:</label>
<input type="text" id="code" name="code" class="form-control" />

<div class="btn-group">
<input type="submit" value="Check" class="btn btn-default" />
<a href="/demos" class="btn btn-default">Back</a>
</div>
</form>
</xsl:template>

</xsl:stylesheet>
