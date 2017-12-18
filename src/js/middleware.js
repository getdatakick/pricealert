// @flow

import type { Settings } from 'types';
import type { Action } from 'actions';
import { merge } from 'ramda';
import { Types, setSnackbar } from 'actions';
import { writeEmail, getLocalId } from 'utils/storage';


export default (settings: Settings) => {
  return (store: any) => (next: any) => (action: Action) => {
    const res = next(action);
    if (action.type === Types.setEmail) {
      writeEmail(action.email);
    }
    if (action.type === Types.submit) {
      const data = merge(action.data, {
        ajax: true,
        lid: getLocalId(),
        action: 'create'
      });
      window.$.ajax({
        url: window.priceAlertUrl.replace('http:', window.location.protocol),
        type: 'post',
        data: data,
        success: () => store.dispatch(setSnackbar(settings.translation.alert_has_been_created))
      });
    }
    return res;
  };
};
