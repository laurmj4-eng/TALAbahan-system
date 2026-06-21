Add-Type -AssemblyName System.Drawing
$img = [System.Drawing.Image]::FromFile("c:\xampp\htdocs\TALAbahan-system\public\images\pic1.jpg")
$newWidth = 1920
$newHeight = [math]::Round($newWidth * $img.Height / $img.Width)
$bmp = New-Object System.Drawing.Bitmap($img, $newWidth, $newHeight)
$codec = [System.Drawing.Imaging.ImageCodecInfo]::GetImageDecoders() | Where-Object { $_.FormatID -eq [System.Drawing.Imaging.ImageFormat]::Jpeg.Guid }
$encoder = [System.Drawing.Imaging.Encoder]::Quality
$ep = New-Object System.Drawing.Imaging.EncoderParameters(1)
$ep.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter($encoder, [long]75)
$bmp.Save("c:\xampp\htdocs\TALAbahan-system\public\images\pic1_compressed.jpg", $codec, $ep)
$img.Dispose()
$bmp.Dispose()
