// @flow

const read = (item: string) => window.localStorage && window.localStorage.getItem(item);
const write = (item: string, value: string) => window.localStorage && window.localStorage.setItem(item, value);

const emailKey = 'phpae';
const localIdKey = 'phpal';

export const readEmail = (): string => read(emailKey) || '';

export const writeEmail = (email: string) => write(emailKey, email);

export const getLocalId = () => {
  let lid = read(localIdKey);
  if (!lid) {
    lid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
      return v.toString(16);
    });
    write(localIdKey, lid);
  }
  return lid;
};
