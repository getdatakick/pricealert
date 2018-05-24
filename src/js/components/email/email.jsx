// @flow

import type { Settings } from 'types';
import React from 'react';
import TextField from 'material-ui/TextField';
import Checkbox from 'material-ui/Checkbox';
import Icon from 'material-ui/svg-icons/communication/email';
import styles from 'cleanslate.less';

type Props = {
  email: string,
  valid: boolean,
  settings: Settings,
  setEmail: (string)=>void,
  onAgree: (boolean)=>void,
  onSubmit: ()=>void
}

class Email extends React.PureComponent<Props> {
  static displayName = 'Email';

  render() {
    const { email, valid, settings, onSubmit, setEmail } = this.props;
    const consent = settings.config.consent;
    const translation = settings.translation;
    const text = translation.your_email_address;
    return (
      <div className={styles.email}>
        <div>
          <Icon className={styles.icon} style={{width: null, height: null}} color="#999"/>
        </div>
        <div>
          <TextField
            ref={(e) => e && e.focus()}
            value={email}
            fullWidth={true}
            floatingLabelText={text}
            hintText={text}
            onKeyPress={e => valid && "Enter" == e.key && onSubmit()}
            onChange={e => setEmail(e.target.value)} />
        </div>
        {consent && this.renderConsent(consent)}
      </div>
    );
  }

  renderConsent = (consent: string) => {
    const markup = { __html: consent };
    const msg = <span dangerouslySetInnerHTML={markup} />;
    return (
      <div className={styles.gdpr}>
        <Checkbox
          label={msg}
          onCheck={(e, value) => this.props.onAgree(value)} />
      </div>
    );
  }
}

export default Email;
