type Nullable<T> = T | null;

interface Indexable<T = any> {
    [key: string]: T;
}

type StrictRecord<K extends PropertyKey, T = any> = {
    readonly [key in K]: T;
};

type AtLeastOneRecord<K extends PropertyKey, T = any> = {
    [P in K]: { readonly [Q in P]: T } & { readonly [Q in Exclude<K, P>]?: T };
}[K];

export {
    type Nullable,
    type Indexable,
    type AtLeastOneRecord,
    type StrictRecord,
};
