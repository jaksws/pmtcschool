# PDF Header Footer plugin

![screenshot](https://gitlab.com/francoisjacquet/PDF_Header_Footer/raw/master/screenshot.png?inline=false)

https://www.rosariosis.org/plugins/pdf-header-footer/

Version 10.1 - May, 2023

Author François Jacquet

Sponsored by Aquarelle private school

License Gnu GPL v2

## Description

This RosarioSIS plugin lets you define and add a custom, rich text header and / or footer to PDF documents generated by RosarioSIS.
Pages generated using the "Print" button can be excluded.

Translated in [French](https://www.rosariosis.org/fr/plugins/pdf-header-footer/), [Spanish](https://www.rosariosis.org/es/plugins/pdf-header-footer/) and Portuguese (Brazil).

## Content

Plugin Configuration

- Add custom header.
- Add custom footer.
- Adjust bottom & top margins.
- Exclude PDF generated using the "Print" button (limit footer and header to school documents only).

## Tip

If the header or footer image does not appear on the PDF, please try to resize it down and increase the margin size.

## Install

Copy the `PDF_Header_Footer/` folder (if named `PDF_Header_Footer-master`, rename it) and its content inside the `plugins/` folder of RosarioSIS.

Go to _School > Configuration > Plugins_ and click "Activate".

Requires RosarioSIS 4.4+ and [wkhtmltopdf](https://wkhtmltopdf.org/)
