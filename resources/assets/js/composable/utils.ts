import Location from 'Interfaces/utils';
import { customAlphabet } from 'nanoid';

export function getLocation(data: Location[]) {
  let locations: string[] = [];

  locations = data.map((item) => {
    return item.reference;
  });

  const lastLocation = locations.slice(-1)[0];
  locations = locations.slice(0, -1);

  if (locations.length > 0) {
    return locations.join(', ') + ' ' + 'and' + ' ' + lastLocation;
  } else {
    return lastLocation;
  }
}

export function countDocumentLink(document_link) {
  let documentCount = 0;

  for (const document in document_link) {
    const result = reduceDocumentLink(document_link[document], []);

    if (!result.every((item) => item === null)) {
      documentCount++;
    }
  }

  return documentCount;
}

export function reduceDocumentLink(document_link, values) {
  if (typeof document_link === 'object' && document_link) {
    for (const key in document_link) {
      values.concat(reduceDocumentLink(document_link[key], values));
    }
  } else {
    values.push(document_link);
  }

  return values;
}

export function isJson(str) {
  try {
    JSON.parse(str);
  } catch (e) {
    return false;
  }
  return true;
}
export function sentenceCaseToKebabCase(str) {
  return str.split(' ').join('-');
}

export function snakeCaseToSentenceCase(str) {
  const words = str.split('_');
  let sentence =
    words[0].charAt(0).toUpperCase() + words[0].slice(1).toLowerCase();

  for (let i = 1; i < words.length; i++) {
    sentence += ' ' + words[i].toLowerCase();
  }

  return sentence;
}

export function kebabCaseToSnakecase(str) {
  return str.replace(/-/g, '_');
}

export function truncateText(text, maxLength) {
  if (text.length > maxLength) {
    return text.substring(0, maxLength) + '...';
  }
  return text;
}

export function generateUsername(fullname: string) {
  if (fullname.length > 0) {
    const snakeCaseString = fullname.toLowerCase().replace(/\s+/g, '_');
    const randomDigits = customAlphabet('0123456789', 2);

    return snakeCaseString + '_' + parseInt(randomDigits());
  }

  return '';
}

export function onlyDeprecatedStatusMap(elements) {
  const deprecatedStatus = [];

  for (let i = 0; i < Object.keys(elements).length; i++) {
    if (i in elements && 'deprecation_status_map' in elements[i]) {
      // eslint-disable-next-line @typescript-eslint/ban-ts-comment
      // @ts-ignore
      deprecatedStatus.push(elements[i]['deprecation_status_map']);
    }
  }

  return deprecatedStatus;
}

export function isEveryValueNull(data): boolean {
  if (Array.isArray(data)) {
    return data.every((item) => isEveryValueNull(item));
  } else if (typeof data === 'object' && data !== null) {
    return Object.values(data).every((value) => isEveryValueNull(value));
  } else {
    return data === null;
  }
}

export function getTranslatedUntitled(translatedData): string {
  return translatedData ? translatedData['common.common.untitled'] : 'Untitled';
}

export function getTranslatedMissing(translatedData, element = ''): string {
  if (!translatedData) {
    return toTitleCase(element) + ' ' + 'Not Entered';
  }

  let returnValue = translatedData['common.common.missing'];

  if (element) {
    returnValue =
      (getTranslatedElement(translatedData, element) ?? element) +
      ' ' +
      translatedData['common.common.missing'];
  }

  return toTitleCase(returnValue);
}

export function getTranslatedLanguage(translatedData): string {
  return translatedData
    ? toTitleCase(translatedData['elements.label.language'])
    : 'Language';
}

export function getTranslatedDeleteElement(translatedData, element = '') {
  let returnValue = translatedData['common.common.delete'];

  if (element) {
    returnValue =
      translatedData['common.common.delete'] +
        ' ' +
        getTranslatedElement(translatedData, element) ?? element;
  }

  return toTitleCase(returnValue);
}
export function getTranslatedElement(translatedData, element: string): string {
  if (!translatedData) {
    return toTitleCase(element);
  }

  return toTitleCase(translatedData[`elements.label.${element}`] ?? '');
}

export function toTitleCase(word: string): string {
  return word?.replace(
    /\w\S*/g,
    (txt) => txt.charAt(0).toUpperCase() + txt.slice(1).toLowerCase()
  );
}

export function toKebabCase(word: string): string {
  return word.replace(/_/g, '-').toLowerCase();
}
