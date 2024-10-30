window.onload = function() {
  const getTokenData = function(value) {
    jQuery.get('https://graph.contentfry.com/v2.0/accesstoken?access_token=' + value, function(response) {
      if (response && response.data) {
        const { team_name: teamName, name, token } = response.data;
        const container = document.getElementById('cf-create-tokens');

        const { tokensCount, tokens, clickToConnect } = CF; // eslint-disable-line

        const tokenExist = tokens.find(obj => obj.token === token);

        const html = `${container.innerHTML}
        <div class="cf-token ${tokenExist ? 'cf-token-error' : 'cf-token-new'}">
          ${tokenExist ? '<span class="error">This token already exist!</span>' : ''}
          <h3>${teamName} <small>${name}</small></h3>
          <input type="text" class="hidden" name="ctf_api_settings[tokens][${tokensCount}][team_name]" value="${teamName}">
          <input type="text" class="hidden" name="ctf_api_settings[tokens][${tokensCount}][name]" value="${name}">
          <input type="text" class="widefat" name="ctf_api_settings[tokens][${tokensCount}][token]" value="${token}" readonly>
          ${!tokenExist ? '<button type="button" class="button button-primary" data-action="save">Save</button>' : `${clickToConnect}`}
        </div>`;
        container.innerHTML = html;
      }
    });
  };

  const createBtn = document.getElementById('cf-create-token');
  const createTokenInput = document.getElementById('cf-access-token');

  // delete token
  document.getElementById('cf-tokens').addEventListener('click', function(e) {
    if (e.target.getAttribute('data-action') === 'delete') {
      document.getElementById('cf-create-tokens').innerHTML = '';
      e.target.parentElement.remove();
      jQuery(document.getElementById('cf-settings-form-submit')).trigger('click');
    }
  }, false);

  // create token
  document.getElementById('cf-create-tokens').addEventListener('click', function(e) {
    if (e.target.getAttribute('data-action') === 'save') {
      jQuery(document.getElementById('cf-settings-form-submit')).trigger('click');
      setTimeout(() => {
        window.location = CF.adminUrl; // eslint-disable-line
      }, 300);
    }
  }, false);

  if (createTokenInput.value !== '') {
    getTokenData(createTokenInput.value);
  }

  if (createBtn) {
    createBtn.addEventListener('click', function() {
      jQuery(document.getElementById('cf-settings-form-submit')).trigger('click');
    });
  }
};
