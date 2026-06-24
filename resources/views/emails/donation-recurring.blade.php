<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
  <meta charset="utf-8">
  <meta name="x-apple-disable-message-reformatting">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="format-detection" content="telephone=no, date=no, address=no, email=no, url=no">
  <meta name="color-scheme" content="light dark">
  <meta name="supported-color-schemes" content="light dark">
  <!--[if mso]>
  <noscript>
    <xml>
      <o:OfficeDocumentSettings xmlns:o="urn:schemas-microsoft-com:office:office">
        <o:PixelsPerInch>96</o:PixelsPerInch>
      </o:OfficeDocumentSettings>
    </xml>
  </noscript>
  <style>
    td,th,div,p,a,h1,h2,h3,h4,h5,h6 {font-family: "Segoe UI", sans-serif; mso-line-height-rule: exactly;}
  </style>
  <![endif]-->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" media="screen">
  <style>
    @media (min-width: 640px) {
      .sm-p-6 {
        padding: 1.5rem;
      }
      .sm-px-4 {
        padding-left: 1rem;
        padding-right: 1rem;
      }
      .sm-px-6 {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
      }
    }
  </style>
</head>
<body style="margin: 0px; width: 100%; background-color: rgb(255, 251, 235); padding: 0px; -webkit-font-smoothing: antialiased; word-break: break-word;">
  <div role="article" aria-roledescription="email" lang="en">
    <div class="sm-px-4" style="background-color: rgb(255, 251, 235); font-family: Inter, ui-sans-serif, system-ui, -apple-system, sans-serif">
      <table align="center" style="margin: 0px auto;" cellpadding="0" cellspacing="0" role="none">
        <tr>
          <td style="width: 600px; max-width: 100%;">
            <div style="height: 1.5rem;"></div>
            <table role="presentation" style="width: 100%;" cellpadding="0" cellspacing="0">
              <tr>
                <td style="border-top-left-radius: 1rem; border-top-right-radius: 1rem; padding: 1.25rem 2.5rem; text-align: center; background-color: {{ $accentColor }};" class="sm-px-6">
                  @if($logoUrl)
                  <img src="{{ $logoUrl }}" alt="{{ $orgName }}" width="160" style="max-width: 100%; vertical-align: middle; margin-left: auto; margin-right: auto;">
                  @elseif($orgName)
                  <p style="margin: 0px; font-size: 1.25rem; line-height: 1.75rem; font-weight: 800; letter-spacing: -0.025em; color: rgb(255, 255, 255);">{{ $orgName }}</p>
                  @endif
                </td>
              </tr>
            </table>
            <table role="presentation" style="width: 100%;" cellpadding="0" cellspacing="0">
              <tr>
                <td class="sm-p-6" style="border-bottom-right-radius: 1rem; border-bottom-left-radius: 1rem; background-color: rgb(255, 255, 255); padding: 2.5rem; border: 1px solid #fde68a">
                  <h1 style="margin: 0px 0px 0.5rem; font-size: 1.875rem; line-height: 2.25rem; font-weight: 800; letter-spacing: -0.025em; color: rgb(15, 23, 42);">
                    {{ $heading }}
                  </h1>
                  <div style="height: 1rem;"></div>
                  <p style="margin: 0px 0px 1rem; font-size: 1rem; line-height: 1.75rem; color: rgb(71, 85, 105);">
                    {{ $greeting }}
                  </p>
                  <p style="margin: 0px 0px 1.5rem; font-size: 1rem; line-height: 1.75rem; color: rgb(71, 85, 105);">
                    {{ $body }}
                  </p>
                  <table role="presentation" style="width: 100%; border-radius: 0.75rem; background-color: {{ $accentColor }}10;" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding: 1.25rem 1.5rem; text-align: center;">
                        <p style="margin: 0px; font-size: 0.875rem; line-height: 1.25rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: rgb(100, 116, 139);">Monthly donation</p>
                        <p style="margin: 0.25rem 0px 0px; font-size: 1.875rem; line-height: 2.25rem; font-weight: 800; color: {{ $accentColor }};">{{ strtoupper($currency) }} {{ number_format($amount, 2) }}<span style="font-size: 1.125rem; line-height: 1.75rem; font-weight: 700; color: rgb(148, 163, 184);">/mo</span></p>
                      </td>
                    </tr>
                  </table>
                  <div style="height: 2rem;"></div>
                  <table role="presentation" style="width: 100%; border-top: 2px solid #fef3c7;" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding-top: 1.5rem;">
                        <p style="margin: 0px; font-size: 1rem; line-height: 1.5rem; color: rgb(100, 116, 139);">
                          With gratitude,
                          <br>
                          <span style="font-weight: 700; color: rgb(51, 65, 85);">{{ $orgName }}</span>
                        </p>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <table role="presentation" style="width: 100%;" cellpadding="0" cellspacing="0">
              <tr>
                <td class="sm-px-6" style="padding: 1.5rem 2.5rem; text-align: center">
                  <p style="margin: 0px; font-size: 0.75rem; line-height: 1rem; color: rgba(217, 119, 6, 0.6);">
                    You received this email because you made a donation.
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>
  </div>
</body>
</html>