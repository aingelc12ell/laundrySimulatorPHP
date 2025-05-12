I asked different AIs to do the following

```
Write a PHP script, using OOP, that simulates the following given a number of sets:

Steps:
1. Soap Wash. 15 minutes
2. Spin Dry. 3 minutes
3. Water Wash. 10 minutes
4. Hang Dry. 5 minutes

Rules:
a. Steps are done sequentially for every set.
b. Only one set can be processed in every step.
c. Transition from one step to another takes 1 minute. No other steps can start while a transition is on-going.
d. Each step has to be completed when started without interruption.
e. Water Wash can be done while the other steps are on-going, except while a transition is on-going.
f. Soap Wash can be started for the next set only when Spin Dry or Hang Dry is about to start.
g. Steps can be started in parallel except for Spin Dry and Hang Dry. Hang Dry takes higher priority than Spin Dry.

The script provides a timeline in minutes, each step is started and finished for every set.
The script can also determine how many sets can be completed in a given number of minutes.
```

Each of the AI's response are stored on their own directory.

**TLDR**: ChatGPT's v2 best time for 5 sets is 124 minutes while Qwen's at 120 minutes


### Qwen
```
🚀 Starting simulation for 5 sets...
▶️ 0     Set 0, Soap Wash, ends at 15
✅  15          Set 0, Completed: Soap Wash
🔄 15    Transition started at ends at 16.
➡️ 16   Set 0   Transition ended, proceeding to step 1 .
▶️ 16    Set 0, Spin Dry, ends at 19
▶️ 16            Set 1, Soap Wash, ends at 31
✅  19          Set 0, Completed: Spin Dry
🔄 19    Transition started at ends at 20.
➡️ 20   Set 0   Transition ended, proceeding to step 2 .
▶️ 20    Set 0, Water Wash, ends at 30
✅  30          Set 0, Completed: Water Wash
🔄 30    Transition started at ends at 31.
✅  31                  Set 1, Completed: Soap Wash
🔄 31            Transition started at ends at 32.
➡️ 31   Set 0   Transition ended, proceeding to step 3 .
▶️ 32    Set 0, Hang Dry, ends at 37
➡️ 32           Set 1   Transition ended, proceeding to step 1 .
▶️ 37            Set 1, Spin Dry, ends at 40
▶️ 37                    Set 2, Soap Wash, ends at 52
✅  37          Set 0, Completed: Hang Dry
🔄 37    Transition started at ends at 38.
➡️ 38   Set 0   Transition ended, proceeding to step 4 .
✅  40                  Set 1, Completed: Spin Dry
🔄 40            Transition started at ends at 41.
➡️ 41           Set 1   Transition ended, proceeding to step 2 .
▶️ 41            Set 1, Water Wash, ends at 51
✅  51                  Set 1, Completed: Water Wash
🔄 51            Transition started at ends at 52.
✅  52                          Set 2, Completed: Soap Wash
🔄 52                    Transition started at ends at 53.
➡️ 52           Set 1   Transition ended, proceeding to step 3 .
▶️ 53            Set 1, Hang Dry, ends at 58
➡️ 53                   Set 2   Transition ended, proceeding to step 1 .
▶️ 58                    Set 2, Spin Dry, ends at 61
▶️ 58                            Set 3, Soap Wash, ends at 73
✅  58                  Set 1, Completed: Hang Dry
🔄 58            Transition started at ends at 59.
➡️ 59           Set 1   Transition ended, proceeding to step 4 .
✅  61                          Set 2, Completed: Spin Dry
🔄 61                    Transition started at ends at 62.
➡️ 62                   Set 2   Transition ended, proceeding to step 2 .
▶️ 62                    Set 2, Water Wash, ends at 72
✅  72                          Set 2, Completed: Water Wash
🔄 72                    Transition started at ends at 73.
✅  73                                  Set 3, Completed: Soap Wash
🔄 73                            Transition started at ends at 74.
➡️ 73                   Set 2   Transition ended, proceeding to step 3 .
▶️ 74                    Set 2, Hang Dry, ends at 79
➡️ 74                           Set 3   Transition ended, proceeding to step 1 .
▶️ 79                            Set 3, Spin Dry, ends at 82
▶️ 79                                    Set 4, Soap Wash, ends at 94
✅  79                          Set 2, Completed: Hang Dry
🔄 79                    Transition started at ends at 80.
➡️ 80                   Set 2   Transition ended, proceeding to step 4 .
✅  82                                  Set 3, Completed: Spin Dry
🔄 82                            Transition started at ends at 83.
➡️ 83                           Set 3   Transition ended, proceeding to step 2 .
▶️ 83                            Set 3, Water Wash, ends at 93
✅  93                                  Set 3, Completed: Water Wash
🔄 93                            Transition started at ends at 94.
✅  94                                          Set 4, Completed: Soap Wash
🔄 94                                    Transition started at ends at 95.
➡️ 94                           Set 3   Transition ended, proceeding to step 3 .
▶️ 95                            Set 3, Hang Dry, ends at 100
➡️ 95                                   Set 4   Transition ended, proceeding to step 1 .
▶️ 100                                   Set 4, Spin Dry, ends at 103
✅  100                                 Set 3, Completed: Hang Dry
🔄 100                           Transition started at ends at 101.
➡️ 101                          Set 3   Transition ended, proceeding to step 4 .
✅  103                                         Set 4, Completed: Spin Dry
🔄 103                                   Transition started at ends at 104.
➡️ 104                                  Set 4   Transition ended, proceeding to step 2 .
▶️ 104                                   Set 4, Water Wash, ends at 114
✅  114                                         Set 4, Completed: Water Wash
🔄 114                                   Transition started at ends at 115.
➡️ 115                                  Set 4   Transition ended, proceeding to step 3 .
▶️ 115                                   Set 4, Hang Dry, ends at 120
✅  120                                         Set 4, Completed: Hang Dry
🏁 Simulation completed in 120 minutes
```


## ChatGPT v2
```
  0 | Set 1 | Soap Wash  | end:  15
 16 | Set 1 | Spin Dry   | end:  19
    | Set 2 | Soap Wash  | end:  31
 20 | Set 1 | Water Wash | end:  30
 32 | Set 1 | Hang Dry   | end:  37
 38 | Set 2 | Spin Dry   | end:  41
    | Set 3 | Soap Wash  | end:  53
 42 | Set 2 | Water Wash | end:  52
 54 | Set 2 | Hang Dry   | end:  59
 60 | Set 3 | Spin Dry   | end:  63
    | Set 4 | Soap Wash  | end:  75
 64 | Set 3 | Water Wash | end:  74
 76 | Set 3 | Hang Dry   | end:  81
 82 | Set 4 | Spin Dry   | end:  85
    | Set 5 | Soap Wash  | end:  97
 86 | Set 4 | Water Wash | end:  96
 98 | Set 4 | Hang Dry   | end: 103
104 | Set 5 | Spin Dry   | end: 107
108 | Set 5 | Water Wash | end: 118
119 | Set 5 | Hang Dry   | end: 124

```

