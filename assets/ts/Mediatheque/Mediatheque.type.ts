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
  thumbnail: string;
  children: [
    {
      type: string;
      thumbnail: string;
    },
  ];
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
  folder_id: number;
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

export type Path = { id: number; name: string };
export type Paths = Path[];

export type MediaItem = MediaFolder | MediaFile;

export type MediaList = MediaItem[];
