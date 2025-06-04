# Optha Vision Assessment WordPress Plugin

Current version: 1.7.0

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
The plugin looks for new releases on GitHub at [UniBed/Optha](https://github.com/UniBed/Optha). Release data is cached for one hour to reduce API requests. When a new version is tagged, WordPress will offer it as an update in your dashboard. For smooth updates, attach a zipped file named `optha-vision-assessment.zip` to each GitHub release. This zip should contain the plugin folder so WordPress installs it in the correct directory.

### GitHub Actions Packaging
The workflow in `.github/workflows/package-plugin.yml` builds `optha-vision-assessment.zip` on each push. You can download this artifact from the workflow run and attach it to a new GitHub release.

## Disclaimer
This plugin offers an informal assessment only. It does not constitute medical advice. Always consult a qualified eye care professional for a full examination and recommendation.
