import type { SchemaRow } from '@/ts/DatabaseManager/SchemaDatabase.type';

export interface SchemaTableData {
  result: SchemaRow[];
  header: Record<string, string>;
  error?: string;
  table: string;
}
