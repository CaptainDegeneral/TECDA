import axios from 'axios';

const getRoles = async () => {
    return axios.get(route('role.index'));
};

export { getRoles };
