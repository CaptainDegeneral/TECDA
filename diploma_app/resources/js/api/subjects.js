import axios from 'axios';

const getAllSubjects = async (page, searchValue) => {
    return await axios.get(
        route('subject.index', {
            page: page,
            search_value: searchValue,
        }),
    );
};

const getSubjectsCodeList = async () => {
    return await axios.get(route('subject.list'));
};

const getSubject = async (id) => {
    return await axios.get(
        route('subject.show', {
            id,
        }),
    );
};

const deleteSubject = async (id) => {
    return await axios.delete(
        route('subject.destroy', {
            id,
        }),
    );
};

const editSubject = async (id, form) => {
    return await axios.put(
        route('subject.edit', {
            id,
        }),
        {
            name: form.name,
            code: form.code,
        },
    );
};

const createSubject = async (form) => {
    return await axios.post(route('subject.store'), {
        name: form.name,
        code: form.code,
    });
};

export {
    createSubject,
    deleteSubject,
    editSubject,
    getAllSubjects,
    getSubject,
    getSubjectsCodeList,
};
