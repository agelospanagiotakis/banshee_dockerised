<?xml version="1.0" ?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../banshee/main.xslt" />

<!--
//
//  Content template
//
//-->
<xsl:template match="content">
<h1>Input validation</h1>
<xsl:call-template name="show_messages" />
<form action="/{/output/page}" method="post">
<table class="edit">
<label for="string">String:</label>
<input type="text" id="string" name="string" value="{string}" class="form-control" />
<label for="number">Number:</label>
<input type="text" id="number" name="number" value="{number}" class="form-control" />
<label for="enum">Enum:</label>
<input type="text" id="name" name="enum" value="{enum}" class="form-control" />
</table>

<div class="btn-group">
<input type="submit" value="Validate data" class="btn btn-default" />
<a href="/demos" class="btn btn-default">Back</a>
</div>
</form>
</xsl:template>

</xsl:stylesheet>
