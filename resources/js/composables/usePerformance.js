export function runHeavyTaskWithoutBlockingUI(task) {
  return new Promise((resolve) => {
    if (typeof requestIdleCallback !== 'undefined') {
      requestIdleCallback(
        () => {
          try {
            const result = task();
            resolve(result);
          } catch (e) {
            resolve(undefined);
          }
        },
        { timeout: 1000 }
      );
    } else {
      setTimeout(() => {
        try {
          const result = task();
          resolve(result);
        } catch (e) {
          resolve(undefined);
        }
      }, 0);
    }
  });
}
