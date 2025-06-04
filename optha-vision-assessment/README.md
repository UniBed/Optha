# Optha Vision Assessment WordPress Plugin

Current version: 1.6.0

This plugin provides a conversational vision assessment that can be embedded via the `[vision_assessment]` shortcode. Submissions are stored as a custom post type and emailed to the site administrator. Fun eye facts and an estimated "eye age" are displayed after completion.

## Features
- Conversational form styled to match Divi
- Animated, step-by-step questions for a more engaging experience
- Simple vision tasks (reading letters and identifying orientation) to gauge eyesight
- Estimated eye age with playful disclaimer
- Stores leads and emails results to the admin
- Checks GitHub for updates automatically with transient caching

## Installation
1. Upload the `optha-vision-assessment` folder to your WordPress `plugins` directory.
2. Activate the plugin through the Plugins menu.
3. Add the shortcode `[vision_assessment]` to any page or post.

### Automatic Updates
The plugin looks for new releases on GitHub at [UniBed/Optha](https://github.com/UniBed/Optha). Release data is cached for one hour to reduce API requests. When a new version is tagged, WordPress will offer it as an update in your dashboard.

## Disclaimer
This plugin offers an informal assessment only. It does not constitute medical advice. Always consult a qualified eye care professional for a full examination and recommendation.
