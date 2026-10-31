

Normally the site loads Bootstrap, jQuery, Bootstrap Icons and Google Fonts
from the internet. If the demo room has no internet, do this ONCE on a
computer that does, then copy the files into this folder:

  1. https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css
       -> save as  assets/vendor/bootstrap.min.css
  2. https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js
       -> save as  assets/vendor/bootstrap.bundle.min.js
  3. https://code.jquery.com/jquery-3.7.1.min.js
       -> save as  assets/vendor/jquery.min.js
  4. https://github.com/twbs/icons/releases/download/v1.11.3/bootstrap-icons-1.11.3.zip
       -> unzip, then copy the folder "font" so that you have
          assets/vendor/bootstrap-icons/bootstrap-icons.min.css
          assets/vendor/bootstrap-icons/fonts/bootstrap-icons.woff2
          assets/vendor/bootstrap-icons/fonts/bootstrap-icons.woff

  (Right-click each link in the browser -> "Save link as…")

Then open config/config.local.php and add:

  define('USE_LOCAL_LIBS', true);


