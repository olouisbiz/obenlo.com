# 🚀 Obenlo Deployment Guide

This repository contains the **Obenlo Core Plugin** and the **Obenlo SES Theme**. 
Since GitHub is not synced to SiteGround, use the following workflow:

## 🛠 1. The Build Process
Run this command in the terminal to generate fresh, clean ZIP files:
```bash
rm -f *.zip
zip -r obenlo-core-plugin.zip obenlo-plugin/ -x "*.git*"
zip -r obenlo-core-theme.zip obenlo-theme/ -x "*.git*"
```

## 📤 2. SiteGround Upload
1. **Plugin:** - Go to WP Admin > Plugins > Add New > Upload.
   - If an old version exists, select "Replace current with uploaded."
2. **Theme:** - Go to WP Admin > Appearance > Themes > Add New > Upload.
   - Activate "Obenlo SES."

## ⚙️ 3. Critical Configuration
After upload, ensure these three things are done:
1. **Permalinks:** Go to Settings > Permalinks and click "Save Changes" (flushes the URL cache).
2. **Stripe Keys:** Go to Obenlo SES > Settings and enter your API keys.
3. **Account Page:** Create a page with the slug `account` (No content/shortcodes needed).

## 🪝 4. Webhook Setup
Set your Stripe Webhook URL to:
`https://yourdomain.com/?obenlo-listener=stripe`
