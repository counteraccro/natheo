export type SchemaRow = Record<string, string | number | boolean | null>;

export interface SchemaDatabaseData {
  result: SchemaRow[];
  header: Record<string, string>;
  error: string;
  stat: {
    nbElement: number;
    sizeBite: string;
    nbTable: number;
  };
}
