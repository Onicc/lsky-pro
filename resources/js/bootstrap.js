import axios from 'axios';
import $ from 'jquery';
import Swal from 'sweetalert2';
import toastr from 'toastr';

window.$ = window.jQuery = $;
window.axios = axios;
window.toastr = toastr;
window.Swal = Swal.mixin({
    showCancelButton: true,
    confirmButtonText: '确认',
    cancelButtonText: '取消',
});

toastr.options = {
    closeButton: true,
    newestOnTop: true,
    progressBar: true,
    positionClass: 'toast-bottom-right',
    escapeHtml: true,
};

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.interceptors.response.use(response => response, error => {
    if (error.response?.status === 401) {
        toastr.warning('登录已失效，请重新登录');
    }

    return Promise.reject(error);
});
