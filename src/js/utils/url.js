// @flow
import type { Attribute, Attributes, AttributeValues } from 'types';
import { reduce, assoc, indexBy, prop, map, pipe } from 'ramda';

const getKey = (id: number):string => `${id}`;

const reverseAttributes = (attributes: Attributes) => {
  let reverse = {};
  for (let i=0; i<attributes.length; i++) {
    const { id, values } = attributes[i];
    for (let j=0; j<values.length; j++) {
      const key = getKey(values[j].id);
      reverse[key] = id;
    }
  }
  return reverse;
};

const getFirstAttributeValue = (attribute: Attribute) => attribute.values[0].id;

export const getInitialAttributes = pipe(
  indexBy(prop('id')),
  map(getFirstAttributeValue)
);

export const getAttributes = (url: string, attributes: Attributes): AttributeValues => {
  const tabParams = url.split('/');
  const reverse = reverseAttributes(attributes);
  return reduce((ret, param) => {
    const value = parseInt(param);
    const id = reverse[getKey(value)];
    return id ? assoc(id, value, ret) : ret;
  }, getInitialAttributes(attributes), tabParams);
};
