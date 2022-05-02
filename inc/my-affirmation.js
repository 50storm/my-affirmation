const onClickAdd = (e) =>{ 
    e.preventDefault();
    document.querySelector('#mode').value = 'add';
    document.querySelector('#affirmationform').action = 'options-general.php?page=my_affirmation&mode=add';
    document.querySelector('#affirmationform').submit();
  };
  const onClickUpdate = (e) =>{ 
    e.preventDefault();
    document.querySelector('#mode').value = 'update';
    const id = document.querySelector('#id').value;
    document.querySelector('#affirmationform').action = `options-general.php?page=my_affirmation&mode=show&id=${id}&action=update`;
    let wp_http_referer = document.querySelector("input[name=_wp_http_referer]").value;
    let new_wp_http_referer = '';
    if (wp_http_referer.includes('&action=delete')) {
      new_wp_http_referer = wp_http_referer.replace(/&action=delete/, '&action=update')
    } else if ( !wp_http_referer.includes('&action=update') ) {
      new_wp_http_referer = wp_http_referer + '&action=update';        
    }
    document.querySelector("input[name=_wp_http_referer]").value = new_wp_http_referer;
    document.querySelector('#affirmationform').submit();
  };
  const onClickDelete = (e) =>{ 
    e.preventDefault();
    if (confirm('削除しますか？')) {
      document.querySelector('#mode').value = 'delete';
      const id = document.querySelector('#id').value;
      document.querySelector('#affirmationform').action = `options-general.php?page=my_affirmation&mode=show&id=${id}&action=delete`;
      let wp_http_referer = document.querySelector("input[name=_wp_http_referer]").value;
      let new_wp_http_referer = '';
      if (wp_http_referer.includes('&action=update')) {
        new_wp_http_referer = wp_http_referer.replace(/&action=update/, '&action=delete')
      } else if ( !wp_http_referer.includes('&action=delete') ) {
        new_wp_http_referer = wp_http_referer + '&action=delete';        
      }
      document.querySelector("input[name=_wp_http_referer]").value = new_wp_http_referer;
      document.querySelector('#affirmationform').submit();
    }
  };
  window.addEventListener('DOMContentLoaded', (event) => {
    document.querySelector('#insertButton').addEventListener('click', onClickAdd);
    document.querySelector('#updateButton').addEventListener('click', onClickUpdate);
    document.querySelector('#deletButton').addEventListener('click', onClickDelete);
  });