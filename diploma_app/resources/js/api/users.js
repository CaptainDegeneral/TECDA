import axios from 'axios';

const getUsers = async (page, searchValue) => {
    return axios.get(
        route('user.index', {
            page: page,
            search_value: searchValue,
        }),
    );
};

const getUser = async (id) => {
    return axios.get(
        route('user.show', {
            id,
        }),
    );
};

const editUser = async (id, form) => {
    return axios.put(
        route('user.edit', {
            id,
        }),
        {
            last_name: form.last_name,
            name: form.name,
            surname: form.surname,
            email: form.email,
            role_id: form.role_id,
            password: form.password,
            verified: form.verified,
        },
    );
};

const createUser = async (form) => {
    return axios.post(route('user.store'), {
        last_name: form.last_name,
        name: form.name,
        surname: form.surname,
        email: form.email,
        password: form.password,
        role_id: form.role_id,
    });
};

const deleteUser = async (id) => {
    return axios.delete(
        route('user.destroy', {
            id,
        }),
    );
};

export { createUser, deleteUser, editUser, getUser, getUsers };
