export type TranslateRecord = { [key: string]: string | TranslateRecord };

export type MediaFolder = {
  type: 'folder';
  id: number;
  parent: number;
  name: string;
  created_at: number;
  date: string;
  size: string;
  nb_elements: number;
  nb_files: number;
  children: [];
};

export type MediaFile = {
  type: 'media';
  id: number;
  name: string;
  description: string;
  size: string;
  webPath: string;
  thumbnail: string;
  created_at: number;
  date: string;
  extension: string;
  img_size: string;
  folder: string;
  folder_id: string;
  title: string;
};

export interface FileData {
  name: string;
  size: number;
  type: string;
  fileExtention: string;
  url: string;
  isImage: boolean;
  isUploaded: boolean;
  title: string;
  description: string;
}

export type MediaItem = MediaFolder | MediaFile;

export type MediaList = MediaItem[];
