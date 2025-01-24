/**
 * Get current activity title
 *
 * @return title text
 */

function getActivityTitle(
  data: { language: string; narrative: string }[],
  language: string
) {
  const translatedUntiled = {
    en: 'Untitled',
    fr: 'Sin título',
    es: 'Sans titre',
  };

  try {
    let title = translatedUntiled[language];

    // title return if language exist in data
    if (data) {
      for (const t of data) {
        if (t.language && t.language === language) {
          title =
            t.narrative && t.narrative !== ''
              ? t.narrative
              : translatedUntiled[language];
          return title;
        }
      }

      // default title return if language does not exist in data
      title =
        data['0'].narrative && data['0'].narrative !== ''
          ? data['0'].narrative
          : translatedUntiled[language];
    }
    return title;
  } catch (e) {
    return 'Untitled';
  }
}

export default getActivityTitle;
