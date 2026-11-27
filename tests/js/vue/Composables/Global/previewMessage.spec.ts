import { describe, expect, it, vi } from 'vitest';
import { postPreviewMessage, readPreviewMessage } from '@/vue/Composables/Global/previewMessage';

const frame = {} as Window;
const eventOf = (data: unknown, values: Partial<MessageEvent> = {}) =>
  ({ data, origin: window.location.origin, source: frame, ...values }) as unknown as MessageEvent;

describe('previewMessage', () => {
  it('envoie un message marqué à la même origine', () => {
    const target = { postMessage: vi.fn() } as unknown as Window;
    postPreviewMessage(target, { type: 'data', data: { id: 1 } });
    expect(target.postMessage).toHaveBeenCalledWith(
      { source: 'natheo-preview', type: 'data', data: { id: 1 } },
      window.location.origin
    );
  });

  it('ne lit que les messages marqués venus du cadre attendu et de la même origine', () => {
    const message = { source: 'natheo-preview', type: 'height', height: 40 };
    expect(readPreviewMessage(eventOf(message), frame)).toEqual(message);
    expect(readPreviewMessage(eventOf(message, { origin: 'https://ailleurs.test' }), frame)).toBeNull();
    expect(readPreviewMessage(eventOf(message, { source: {} as Window }), frame)).toBeNull();
    expect(readPreviewMessage(eventOf({ ...message, source: 'autre' }), frame)).toBeNull();
    expect(readPreviewMessage(eventOf(message), null)).toBeNull();
  });
});
